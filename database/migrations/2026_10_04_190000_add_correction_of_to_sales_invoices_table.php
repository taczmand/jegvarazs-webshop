<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('correction_of_sales_invoice_id')
                ->nullable()
                ->after('storno_of_sales_invoice_id')
                ->constrained('sales_invoices')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index(['correction_of_sales_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['correction_of_sales_invoice_id']);
            $table->dropIndex(['correction_of_sales_invoice_id']);
            $table->dropColumn('correction_of_sales_invoice_id');
        });
    }
};
