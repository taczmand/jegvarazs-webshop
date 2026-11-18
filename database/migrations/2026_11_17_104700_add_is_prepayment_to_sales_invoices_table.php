<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->string('document_type', 50)->default('final')->after('invoice_type')->comment('Bizonylat fajtája: final (végszámla), prepayment (előleg), proforma (díjbekérő), corrective (helyesbítő), storno');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
    }
};
