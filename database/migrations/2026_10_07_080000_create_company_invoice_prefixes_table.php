<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_invoice_prefixes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('prefix', 32);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'prefix']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('company_invoice_prefix_id')
                ->nullable()
                ->after('company_id')
                ->constrained('company_invoice_prefixes')
                ->nullOnDelete();

            $table->string('company_invoice_prefix', 32)
                ->nullable()
                ->after('company_invoice_prefix_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_invoice_prefix_id');
            $table->dropColumn('company_invoice_prefix');
        });

        Schema::dropIfExists('company_invoice_prefixes');
    }
};
