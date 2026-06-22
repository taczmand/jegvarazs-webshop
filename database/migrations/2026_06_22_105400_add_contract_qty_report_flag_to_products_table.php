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
            $table->boolean('count_in_contract_products_report')
                ->default(true)
                ->after('is_selectable_by_installer')
                ->comment('Ha true, a szerződések termék darabszám riport a termék mennyiségével számol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('count_in_contract_products_report');
        });
    }
};
