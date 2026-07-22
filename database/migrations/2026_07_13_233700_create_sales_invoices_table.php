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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->cascadeOnUpdate()->comment('Cég azonosító (companies.id)');

            $table->string('invoice_number')->comment('Számlaszám');
            $table->string('invoice_type')->nullable()->comment('Számla típusa (pl. normal, proforma, storno)');
            $table->string('status')->default('draft')->comment('Számla állapota (pl. draft, issued, sent, cancelled)');
            $table->string('payment_status')->default('unpaid')->comment('Fizetési állapot (pl. unpaid, partially_paid, paid, overdue)');

            $table->string('partner_name')->comment('Partner neve (pillanatkép)');
            $table->string('partner_tax_number')->nullable()->comment('Partner adószáma');
            $table->string('partner_vat_number')->nullable()->comment('Partner EU VAT száma');
            $table->string('partner_country', 2)->nullable()->comment('Partner országkód (ISO2)');
            $table->string('partner_zip_code')->nullable()->comment('Partner irányítószám');
            $table->string('partner_city')->nullable()->comment('Partner város');
            $table->string('partner_address_line')->nullable()->comment('Partner cím (utca, házszám, emelet, ajtó)');
            $table->string('partner_email')->nullable()->comment('Partner e-mail');
            $table->string('partner_phone')->nullable()->comment('Partner telefonszám');

            $table->string('company_name')->nullable()->comment('Cég neve (pillanatkép)');
            $table->string('company_tax_number')->nullable()->comment('Cég adószáma');
            $table->string('company_country', 2)->nullable()->comment('Cég országkód (ISO2)');
            $table->string('company_zip_code')->nullable()->comment('Cég irányítószám');
            $table->string('company_city')->nullable()->comment('Cég város');
            $table->string('company_address_line')->nullable()->comment('Cég cím');
            $table->string('company_email')->nullable()->comment('Cég e-mail');
            $table->string('company_phone')->nullable()->comment('Cég telefonszám');
            $table->string('company_bank_account')->nullable()->comment('Cég bankszámlaszám');

            $table->string('payment_method')->comment('Fizetési mód');
            $table->string('payment_reference')->nullable()->comment('Fizetési közlemény / hivatkozás');
            $table->string('transaction_id')->nullable()->comment('Tranzakció azonosító (bankkártya/bank)');

            $table->date('issued_at')->nullable()->comment('Kelt / kiállítás dátuma');
            $table->date('fulfilled_at')->nullable()->comment('Teljesítés dátuma');
            $table->date('due_at')->nullable()->comment('Fizetési határidő');
            $table->dateTime('settled_at')->nullable()->comment('Kiegyenlítés dátuma');

            $table->string('currency', 3)->default('HUF')->comment('Pénznem (ISO 4217)');
            $table->decimal('exchange_rate', 14, 6)->nullable()->comment('Árfolyam (ha nem HUF)');
            $table->boolean('prices_include_vat')->default(true)->comment('Igaz: bruttó árak, Hamis: nettó árak');

            $table->integer('net_total')->nullable()->comment('Összes nettó (fillér)');
            $table->integer('vat_total')->nullable()->comment('Összes ÁFA (fillér)');
            $table->integer('gross_total')->nullable()->comment('Összes bruttó (fillér)');
            $table->integer('paid_amount')->nullable()->comment('Befizetett összeg (fillér)');
            $table->integer('outstanding_amount')->nullable()->comment('Kintlévőség (fillér)');
            $table->integer('rounding_amount')->nullable()->comment('Kerekítés összege (fillér)');

            $table->foreignId('related_order_id')->nullable()->constrained('orders')->nullOnDelete()->cascadeOnUpdate()->comment('Kapcsolódó rendelés azonosító (orders.id)');
            $table->foreignId('related_contract_id')->nullable()->constrained('contracts')->nullOnDelete()->cascadeOnUpdate()->comment('Kapcsolódó szerződés azonosító (contracts.id)');
            $table->foreignId('storno_of_sales_invoice_id')->nullable()->constrained('sales_invoices')->nullOnDelete()->cascadeOnUpdate()->comment('Stornó esetén az eredeti kimenő számla azonosítója');

            $table->string('external_provider')->nullable()->comment('Külső szolgáltató neve (pl. szamlazzhu)');
            $table->string('external_id')->nullable()->comment('Külső rendszer azonosítója');
            $table->string('pdf_path')->nullable()->comment('Számla PDF elérési út / storage path');

            $table->text('note_before_items')->nullable()->comment('Megjegyzés a tételek fölé (pl. fejléc szöveg)');
            $table->text('note_after_items')->nullable()->comment('Megjegyzés a tételek alá (pl. záró szöveg)');
            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->unique(['invoice_number']);
            $table->index(['issued_at']);
            $table->index(['status', 'due_at']);
        });

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete()->cascadeOnUpdate()->comment('Kimenő számla azonosító');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->cascadeOnUpdate()->comment('Termék azonosító (products.id), ha terméktörzshöz köthető');

            $table->integer('sort_order')->default(0)->comment('Tétel sorszáma / rendezési kulcs');

            $table->string('name')->comment('Tétel megnevezése');
            $table->string('sku')->nullable()->comment('Cikkszám / SKU (pillanatkép)');
            $table->string('unit')->nullable()->comment('Mértékegység');
            $table->decimal('quantity', 12, 3)->default(1)->comment('Mennyiség');

            $table->integer('unit_net_price')->nullable()->comment('Egységár nettó (fillér)');
            $table->integer('unit_gross_price')->nullable()->comment('Egységár bruttó (fillér)');
            $table->integer('vat_percent')->nullable()->comment('ÁFA %');
            $table->string('vat_code')->nullable()->comment('ÁFA kód / kategória (pl. AAM, EU, reverse)');

            $table->decimal('discount_percent', 6, 2)->nullable()->comment('Kedvezmény %');
            $table->integer('discount_amount')->nullable()->comment('Kedvezmény összeg (fillér)');

            $table->integer('net_total')->nullable()->comment('Sor nettó (fillér)');
            $table->integer('vat_total')->nullable()->comment('Sor ÁFA (fillér)');
            $table->integer('gross_total')->nullable()->comment('Sor bruttó (fillér)');

            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->index(['sales_invoice_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
    }
};
