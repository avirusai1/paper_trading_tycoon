<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Career title definitions mapped to level ranges.
 * Seeded from CareerTitle enum + gamification config.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_titles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->unique();
            $table->unsignedSmallInteger('min_level');
            $table->unsignedSmallInteger('max_level');
            $table->string('description', 300)->nullable();
            $table->string('icon_url', 200)->nullable();
            $table->string('color_hex', 7)->nullable()->comment('UI accent color for this title badge');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['min_level', 'max_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_titles');
    }
};
