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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('company_site_id')->constrained('company_sites')->cascadeOnDelete()->cascadeOnUpdate()->comment('Telephely azonosító (company_sites.id)');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->cascadeOnUpdate()->comment('Termék azonosító (products.id)');

            $table->decimal('quantity', 14, 3)->default(0)->comment('Készleten lévő mennyiség');
            $table->decimal('reserved_quantity', 14, 3)->default(0)->comment('Foglalt mennyiség');
            $table->decimal('available_quantity', 14, 3)->default(0)->comment('Elérhető mennyiség (opcionálisan számított, de tárolható cache-ként)');
            $table->decimal('min_stock_level', 14, 3)->nullable()->comment('Minimum készletszint');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->unique(['company_site_id', 'product_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
