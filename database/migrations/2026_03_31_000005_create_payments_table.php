<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relación polimórfica: puede asociarse a MikposLicense, MikposFeature o CustomProject
            $table->morphs('payable');

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20)->default('cash');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->date('paid_at');

            $table->timestamps();
            $table->softDeletes();

            $table->index('paid_at');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
