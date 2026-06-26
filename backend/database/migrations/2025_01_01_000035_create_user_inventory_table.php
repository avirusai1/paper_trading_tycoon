<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Items owned by each user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('store_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->boolean('is_equipped')->default(false)->comment('For cosmetic items that can be equipped');
            $table->json('metadata')->nullable()->comment('Activation state, expiry for consumables etc.');
            $table->timestamp('expires_at')->nullable()->index()->comment('For time-limited items like XP boosts');
            $table->timestamp('purchased_at')->comment('When the item was purchased');
            $table->timestamps();

            $table->unique(['user_id', 'store_item_id']);
            $table->index(['user_id', 'is_equipped']);
            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_inventory');
    }
};
