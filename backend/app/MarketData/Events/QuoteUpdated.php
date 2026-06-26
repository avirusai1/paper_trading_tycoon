<?php

declare(strict_types=1);

namespace App\MarketData\Events;

use App\MarketData\DTOs\StockQuote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class QuoteUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public StockQuote $quote) {}
}
