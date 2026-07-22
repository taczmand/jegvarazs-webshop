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
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->cascadeOnUpdate()->comment('Cég azonosító (companies.id)');

            $table->string('document_number')->comment('Szállítólevél sorszám / bizonylatszám');
            $table->string('status')->default('draft')->comment('Állapot (pl. draft, issued, delivered, cancelled)');

            $table->string('partner_name')->comment('Partner neve (pillanatkép)');
            $table->string('partner_tax_number')->nullable()->comment('Partner adószáma');
            $table->string('partner_country', 2)->nullable()->comment('Partner országkód (ISO2)');
            $table->string('partner_zip_code')->nullable()->comment('Partner irányítószám');
            $table->string('partner_city')->nullable()->comment('Partner város');
            $table->string('partner_address_line')->nullable()->comment('Partner cím');

            $table->string('company_name')->nullable()->comment('Cég neve (pillanatkép)');
            $table->string('company_tax_number')->nullable()->comment('Cég adószáma');
            $table->string('company_country', 2)->nullable()->comment('Cég országkód (ISO2)');
            $table->string('company_zip_code')->nullable()->comment('Cég irányítószám');
            $table->string('company_city')->nullable()->comment('Cég város');
            $table->string('company_address_line')->nullable()->comment('Cég cím');
            $table->string('company_email')->nullable()->comment('Cég e-mail');
            $table->string('company_phone')->nullable()->comment('Cég telefonszám');
            $table->string('company_bank_account')->nullable()->comment('Cég bankszámlaszám');

            $table->string('shipping_country', 2)->nullable()->comment('Szállítási országkód (ISO2)');
            $table->string('shipping_zip_code')->nullable()->comment('Szállítási irányítószám');
            $table->string('shipping_city')->nullable()->comment('Szállítási város');
            $table->string('shipping_address_line')->nullable()->comment('Szállítási cím');

            $table->date('issued_at')->nullable()->comment('Kelt / kiállítás dátuma');
            $table->date('delivered_at')->nullable()->comment('Kiszállítás / átadás dátuma');

            $table->string('carrier_name')->nullable()->comment('Szállító / fuvarozó neve');
            $table->string('vehicle_plate')->nullable()->comment('Jármű rendszám');
            $table->string('driver_name')->nullable()->comment('Sofőr neve');
            $table->dateTime('handed_over_at')->nullable()->comment('Átadás időpontja');
            $table->string('received_by_name')->nullable()->comment('Átvevő neve');

            $table->text('note_before_items')->nullable()->comment('Megjegyzés a tételek fölé (pl. fejléc szöveg)');
            $table->text('note_after_items')->nullable()->comment('Megjegyzés a tételek alá (pl. záró szöveg)');
            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->unique(['document_number']);
            $table->index(['status', 'delivered_at']);
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('delivery_note_id')->constrained('delivery_notes')->cascadeOnDelete()->cascadeOnUpdate()->comment('Szállítólevél azonosító');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->cascadeOnUpdate()->comment('Termék azonosító (products.id), ha terméktörzshöz köthető');

            $table->integer('sort_order')->default(0)->comment('Tétel sorszáma / rendezési kulcs');
            $table->string('name')->comment('Tétel megnevezése');
            $table->string('sku')->nullable()->comment('Cikkszám / SKU (pillanatkép)');
            $table->string('unit')->nullable()->comment('Mértékegység');
            $table->decimal('quantity', 12, 3)->default(1)->comment('Mennyiség');

            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->index(['delivery_note_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};
