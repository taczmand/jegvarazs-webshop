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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_offerable')->default(false)->after('partner_gross_price')
                ->comment('Ha true, megjelenik a szerződésnél és ajánlat generálásánál');
            $table->boolean('is_selectable_by_installer')->default(false)->after('is_offerable')
                ->comment('Szerelő a szerelésnél ki tudja választani?');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_offerable', 'is_selectable_by_installer']);
        });
    }
};
