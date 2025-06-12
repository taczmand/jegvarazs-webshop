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
            $table->float('partner_gross_price')
                ->default(0)
                ->after('gross_price')
                ->comment('Partneri bruttó ár, ha a termék partner számára kedvezményes áron elérhető. Ez az alapértelmezett bruttó ár, ha partner be van jelentkezve.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('partner_gross_price');
        });
    }
};
