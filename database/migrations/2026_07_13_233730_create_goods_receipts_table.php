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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete()->cascadeOnUpdate()->comment('Partner (szállító) azonosító (clients.id)');
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->cascadeOnUpdate()->comment('Cég azonosító (companies.id)');
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate()->comment('Bevételezést rögzítő felhasználó (users.id)');

            $table->string('document_number')->comment('Bevételezés sorszám / bizonylatszám');
            $table->string('supplier_document_number')->nullable()->comment('Szállító által megadott bizonylatszám');
            $table->string('status')->default('draft')->comment('Állapot (pl. draft, posted, cancelled)');

            $table->string('partner_name')->comment('Partner neve (pillanatkép)');
            $table->string('partner_tax_number')->nullable()->comment('Partner adószáma');
            $table->string('partner_country', 2)->nullable()->comment('Partner országkód (ISO2)');
            $table->string('partner_zip_code')->nullable()->comment('Partner irányítószám');
            $table->string('partner_city')->nullable()->comment('Partner város');
            $table->string('partner_address_line')->nullable()->comment('Partner cím');

            $table->date('received_at')->nullable()->comment('Bevételezés dátuma');

            $table->foreignId('related_purchase_invoice_id')->nullable()->constrained('purchase_invoices')->nullOnDelete()->cascadeOnUpdate()->comment('Kapcsolódó bejövő számla azonosító (purchase_invoices.id)');

            $table->text('note_before_items')->nullable()->comment('Megjegyzés a tételek fölé (pl. fejléc szöveg)');
            $table->text('note_after_items')->nullable()->comment('Megjegyzés a tételek alá (pl. záró szöveg)');
            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->unique(['document_number']);
            $table->index(['client_id', 'received_at']);
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete()->cascadeOnUpdate()->comment('Bevételezés azonosító');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->cascadeOnUpdate()->comment('Termék azonosító (products.id), ha terméktörzshöz köthető');

            $table->integer('sort_order')->default(0)->comment('Tétel sorszáma / rendezési kulcs');

            $table->string('name')->comment('Tétel megnevezése');
            $table->string('sku')->nullable()->comment('Cikkszám / SKU (pillanatkép)');
            $table->string('unit')->nullable()->comment('Mértékegység');
            $table->decimal('quantity', 12, 3)->default(1)->comment('Mennyiség');

            $table->integer('unit_net_price')->nullable()->comment('Egységár nettó (fillér)');
            $table->integer('unit_gross_price')->nullable()->comment('Egységár bruttó (fillér)');
            $table->integer('vat_percent')->nullable()->comment('ÁFA %');
            $table->string('vat_code')->nullable()->comment('ÁFA kód / kategória');

            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->index(['goods_receipt_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
    }
};
