<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikpos_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('license_key', 64)->unique();
            $table->string('billing_cycle', 20)->default('monthly');
            $table->decimal('monthly_rate', 12, 2)->default(50000.00);
            $table->decimal('installation_fee', 12, 2)->default(0.00);
            $table->boolean('is_free_promotion')->default(false);
            $table->string('status', 20)->default('active');

            $table->date('activated_at')->nullable();
            $table->date('next_billing_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_free_promotion');
            $table->index(['client_id', 'is_free_promotion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikpos_licenses');
    }
};
