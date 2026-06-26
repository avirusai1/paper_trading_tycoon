<?php

declare(strict_types=1);

namespace App\MarketData\Support;

use App\MarketData\Contracts\MarketDataProviderContract;
use App\MarketData\Exceptions\ProviderException;
use App\MarketData\Providers\AlphaVantageProvider;
use App\MarketData\Providers\MockProvider;
use App\MarketData\Providers\TwelveDataProvider;
use Exception;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;

final class ProviderCoordinator
{
    /** @var array<string, CircuitBreaker> */
    private array $circuitBreakers = [];

    public function __construct(
        private Container $container,
        private LoggerInterface $logger
    ) {}

    /**
     * Executes an operation on the active healthy provider that implements the requested interface.
     * If the primary provider fails, it triggers circuit breaker and tries the next healthy provider.
     *
     * @template T
     *
     * @param  string  $contract  The FQCN of the provider contract interface
     * @param  callable(mixed): T  $operation
     * @return T
     *
     * @throws Exception
     */
    public function execute(string $contract, callable $operation)
    {
        $providers = $this->getProvidersForContract($contract);
        if (empty($providers)) {
            throw new ProviderException("No providers configured implementing contract: {$contract}");
        }

        $lastException = null;

        foreach ($providers as $provider) {
            $name = $provider->getName();
            $circuitBreaker = $this->getCircuitBreaker($name);

            if (! $circuitBreaker->isAvailable()) {
                $this->logger->warning("Market Data provider '{$name}' is currently blocked by circuit breaker.");
                continue;
            }

            try {
                $result = RetryHelper::retry(fn () => $operation($provider), maxAttempts: 2, initialDelayMs: 50);
                $circuitBreaker->recordSuccess();

                return $result;
            } catch (Exception $e) {
                $this->logger->error("Market Data provider '{$name}' failed: {$e->getMessage()}", [
                    'exception' => $e,
                ]);
                $circuitBreaker->recordFailure();
                $lastException = $e;
            }
        }

        throw new ProviderException(
            'All market data providers failed. Last error: '.($lastException ? $lastException->getMessage() : 'Unknown'),
            0,
            $lastException
        );
    }

    /**
     * @return MarketDataProviderContract[]
     */
    private function getProvidersForContract(string $contract): array
    {
        $primaryName = config('market_data.provider', 'MockProvider');

        $map = [
            'MockProvider' => MockProvider::class,
            'mock' => MockProvider::class,
            'TwelveData' => TwelveDataProvider::class,
            'twelve_data' => TwelveDataProvider::class,
            'AlphaVantage' => AlphaVantageProvider::class,
            'alpha_vantage' => AlphaVantageProvider::class,
        ];

        $candidates = [];

        if (isset($map[$primaryName])) {
            $candidates[] = $map[$primaryName];
        }

        foreach ($map as $name => $class) {
            if ($name !== $primaryName) {
                $candidates[] = $class;
            }
        }

        $resolved = [];
        foreach ($candidates as $class) {
            try {
                $instance = $this->container->make($class);
                if ($instance instanceof MarketDataProviderContract && is_subclass_of($class, $contract)) {
                    $resolved[] = $instance;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $resolved;
    }

    private function getCircuitBreaker(string $name): CircuitBreaker
    {
        if (! isset($this->circuitBreakers[$name])) {
            $this->circuitBreakers[$name] = new CircuitBreaker($name);
        }

        return $this->circuitBreakers[$name];
    }
}
