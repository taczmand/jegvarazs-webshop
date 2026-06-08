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
        Schema::table('streets', function (Blueprint $table) {
            $table->string('zip_code', 20)->nullable()->after('id');

            $table->dropUnique('city_street');
            $table->unique(['zip_code', 'city', 'street_name'], 'zip_city_street');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streets', function (Blueprint $table) {
            $table->dropUnique('zip_city_street');
            $table->unique(['city', 'street_name'], 'city_street');

            $table->dropColumn('zip_code');
        });
    }
};
