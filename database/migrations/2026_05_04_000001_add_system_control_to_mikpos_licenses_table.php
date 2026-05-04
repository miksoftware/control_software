<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mikpos_licenses', function (Blueprint $table) {
            // Token secreto del SYSTEM_ADMIN_TOKEN del sitio MikPOS remoto (encriptado en BD)
            $table->text('system_token')->nullable()->after('site_url');
            // Último estado conocido del sistema remoto (null = desconocido, sin verificar)
            $table->boolean('system_enabled')->nullable()->default(null)->after('system_token');
        });
    }

    public function down(): void
    {
        Schema::table('mikpos_licenses', function (Blueprint $table) {
            $table->dropColumn(['system_token', 'system_enabled']);
        });
    }
};
