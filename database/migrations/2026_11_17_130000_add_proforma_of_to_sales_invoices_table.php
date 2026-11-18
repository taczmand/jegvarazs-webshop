<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('proforma_of_sales_invoice_id')
                ->nullable()
                ->after('prepayment_of_sales_invoice_id')
                ->constrained('sales_invoices')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('Számla esetén a díjbekérő azonosítója');

            $table->index(['proforma_of_sales_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['proforma_of_sales_invoice_id']);
            $table->dropIndex(['proforma_of_sales_invoice_id']);
            $table->dropColumn('proforma_of_sales_invoice_id');
        });
    }
};
