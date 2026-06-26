<?php

declare(strict_types=1);

namespace App\MarketData\Events;

use App\MarketData\DTOs\StockQuote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MarketDataFetched
{
    use Dispatchable, SerializesModels;

    /**
     * @param  StockQuote[]  $quotes
     */
    public function __construct(public array $quotes) {}
}
