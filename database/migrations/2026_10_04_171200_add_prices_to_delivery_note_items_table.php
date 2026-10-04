<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->decimal('net_price', 14, 2)->nullable()->after('quantity');
            $table->decimal('vat_percent', 6, 2)->nullable()->after('net_price');
            $table->decimal('gross_price', 14, 2)->nullable()->after('vat_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn(['net_price', 'vat_percent', 'gross_price']);
        });
    }
};
