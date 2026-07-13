<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->string('name')->comment('Cég neve');
            $table->string('tax_number')->nullable()->comment('Cég adószáma');
            $table->string('vat_number')->nullable()->comment('Cég EU VAT száma');

            $table->string('country', 2)->default('HU')->comment('Országkód (ISO2)');
            $table->string('zip_code')->nullable()->comment('Irányítószám');
            $table->string('city')->nullable()->comment('Város');
            $table->string('address_line')->nullable()->comment('Cím (utca, házszám, emelet, ajtó)');

            $table->string('email')->nullable()->comment('E-mail');
            $table->string('phone')->nullable()->comment('Telefonszám');
            $table->string('bank_account')->nullable()->comment('Bankszámlaszám');

            $table->string('status')->default('active')->comment('Állapot (active/inactive)');
            $table->boolean('is_default')->default(false)->comment('Alapértelmezett cég');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->index(['status']);
            $table->index(['is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
