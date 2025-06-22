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
        Schema::table('contract_products', function (Blueprint $table) {
            $table->integer('product_qty')->default(1)->after('gross_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_products', function (Blueprint $table) {
            $table->dropColumn('product_qty');
        });
    }
};
