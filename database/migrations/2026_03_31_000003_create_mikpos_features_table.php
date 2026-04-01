<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikpos_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('mikpos_license_id')
                ->nullable()
                ->constrained('mikpos_licenses')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_cost', 12, 2);
            $table->string('status', 20)->default('pending');

            $table->date('estimated_delivery_at')->nullable();
            $table->date('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikpos_features');
    }
};
