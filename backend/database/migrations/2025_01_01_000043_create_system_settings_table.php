<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Key-value system configuration.
 * Runtime-mutable settings (maintenance mode, market hours, support links).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('group', 50)->index()->comment('e.g. app, market, notifications, support');
            $table->text('value')->nullable();
            $table->enum('value_type', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->string('description', 300)->nullable();
            $table->boolean('is_public')->default(false)->comment('If true, exposed via the public API');
            $table->timestamps();

            $table->index(['group', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
