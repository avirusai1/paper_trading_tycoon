<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit log of every status transition for an order.
 * Enables full lifecycle replay without touching the orders table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->string('event_type', 50)->comment('e.g. submitted, filled, cancelled, rejected');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->unsignedInteger('quantity')->nullable()->comment('Quantity involved in this event (for fills)');
            $table->unsignedBigInteger('price_paise')->nullable()->comment('Execution price for fill events');
            $table->json('metadata')->nullable()->comment('Extra context — rejection reason, partial fill details');
            $table->timestamp('occurred_at')->useCurrent()->index();
            // No updated_at — append-only
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
    }
};
