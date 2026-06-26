<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Runtime feature flag configuration.
 * Supports global on/off toggles and per-user percentage rollout.
 * FeatureFlagService reads from this table with cache-aside.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('e.g. crypto_trading, options_trading, battle_pass');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false)->index()->comment('Global on/off');
            $table->unsignedTinyInteger('rollout_percentage')->default(0)->comment('0-100; percentage of users who see this feature');
            $table->boolean('premium_only')->default(false)->comment('Feature restricted to premium subscribers');
            $table->json('allowed_user_ids')->nullable()->comment('Explicit user allowlist for QA/beta testing');
            $table->string('group', 50)->nullable()->index()->comment('Feature group for bulk operations');
            $table->timestamps();

            $table->index(['is_enabled', 'rollout_percentage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
