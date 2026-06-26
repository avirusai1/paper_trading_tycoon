<?php

declare(strict_types=1);

namespace App\MarketData\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ProviderChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $oldProvider,
        public string $newProvider,
        public string $reason
    ) {}
}
