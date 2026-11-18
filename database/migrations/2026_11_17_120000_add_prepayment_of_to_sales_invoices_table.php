<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('prepayment_of_sales_invoice_id')
                ->nullable()
                ->after('correction_of_sales_invoice_id')
                ->constrained('sales_invoices')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('Végszámla esetén az előleg számla azonosítója');

            $table->index(['prepayment_of_sales_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['prepayment_of_sales_invoice_id']);
            $table->dropIndex(['prepayment_of_sales_invoice_id']);
            $table->dropColumn('prepayment_of_sales_invoice_id');
        });
    }
};
