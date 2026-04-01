<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Eliminar columnas polimórficas
            $table->dropMorphs('payable');

            // Añadir relación directa con cliente
            $table->foreignId('client_id')->after('id')->constrained()->onDelete('cascade');

            // Añadir categoría de abono (opcional para separar contabilidad)
            $table->string('category', 30)->default('global')->after('client_id');
            
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
            
            // Re-añadir columnas polimórficas (necesita ser nullable para evitar errores si hay datos)
            $table->nullableMorphs('payable');
        });
    }
};
