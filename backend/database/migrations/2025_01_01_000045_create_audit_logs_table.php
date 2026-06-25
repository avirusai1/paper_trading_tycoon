<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * General-purpose audit trail for sensitive model changes.
 * Captures before/after state on key models (User, Wallet, Holding etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->index()->comment('User who triggered the change (null for system)');
            $table->string('event', 50)->index()->comment('created, updated, deleted, restored');
            $table->string('auditable_type', 100)->index()->comment('Eloquent morph type');
            $table->unsignedBigInteger('auditable_id')->index()->comment('Eloquent morph ID');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('url', 2000)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['auditable_type', 'auditable_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
