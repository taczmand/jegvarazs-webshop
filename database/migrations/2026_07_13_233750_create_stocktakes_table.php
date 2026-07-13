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
        Schema::create('stocktakes', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('company_site_id')->constrained('company_sites')->cascadeOnDelete()->cascadeOnUpdate()->comment('Telephely azonosító (company_sites.id)');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate()->comment('Leltárt létrehozó felhasználó (users.id)');
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate()->comment('Leltárt lezáró felhasználó (users.id)');

            $table->string('name')->comment('Leltár megnevezése');
            $table->date('started_at')->nullable()->comment('Leltár kezdésének dátuma');
            $table->dateTime('started_at_time')->nullable()->comment('Leltár kezdésének időpontja');
            $table->date('closed_at')->nullable()->comment('Leltár lezárásának dátuma');
            $table->dateTime('closed_at_time')->nullable()->comment('Leltár lezárásának időpontja');
            $table->string('status')->default('open')->comment('Állapot (open/closed)');
            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->index(['company_site_id', 'status']);
        });

        Schema::create('stocktake_items', function (Blueprint $table) {
            $table->id()->comment('Egyedi azonosító');

            $table->foreignId('stocktake_id')->constrained('stocktakes')->cascadeOnDelete()->cascadeOnUpdate()->comment('Leltár azonosító');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->cascadeOnUpdate()->comment('Termék azonosító (products.id)');
            $table->foreignId('counted_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate()->comment('Számlálást végző felhasználó (users.id)');

            $table->decimal('expected_quantity', 14, 3)->nullable()->comment('Elvárt mennyiség (rendszer szerinti)');
            $table->decimal('counted_quantity', 14, 3)->nullable()->comment('Leltározott mennyiség');
            $table->decimal('difference_quantity', 14, 3)->nullable()->comment('Eltérés mennyisége (counted - expected)');

            $table->dateTime('counted_at')->nullable()->comment('Számlálás időpontja');
            $table->text('note')->nullable()->comment('Megjegyzés');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Létrehozás időpontja');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Módosítás időpontja');

            $table->unique(['stocktake_id', 'product_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocktake_items');
        Schema::dropIfExists('stocktakes');
    }
};
