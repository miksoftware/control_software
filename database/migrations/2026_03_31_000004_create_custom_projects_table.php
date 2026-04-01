<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('contract_value', 12, 2);
            $table->string('status', 20)->default('pending');

            $table->date('start_date')->nullable();
            $table->date('estimated_end_date')->nullable();
            $table->date('actual_end_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_projects');
    }
};
