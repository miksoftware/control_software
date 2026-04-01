<?php

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
        Schema::table('mikpos_licenses', function (Blueprint $table) {
            $table->string('site_url', 500)->nullable()->after('license_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikpos_licenses', function (Blueprint $table) {
            $table->dropColumn('site_url');
        });
    }
};
