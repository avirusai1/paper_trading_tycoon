<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Individual stocks within a watchlist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['watchlist_id', 'stock_id']);
            $table->index(['watchlist_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_items');
    }
};
