<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Season definitions. A season is a fixed competition period (typically 4 weeks).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedSmallInteger('season_number')->unique();
            $table->date('starts_at')->index();
            $table->date('ends_at')->index();
            $table->enum('status', ['upcoming', 'active', 'ended', 'rewards_distributed'])->default('upcoming')->index();
            $table->text('description')->nullable();
            $table->json('special_rules')->nullable()->comment('Season-specific game rule overrides');
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
