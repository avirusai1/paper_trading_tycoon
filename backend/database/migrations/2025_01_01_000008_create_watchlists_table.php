<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User-defined watchlists. A user can have multiple named watchlists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->string('name', 100);
            $table->boolean('is_default')->default(false)->comment('The primary watchlist shown on home screen');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
