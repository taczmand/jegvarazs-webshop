<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->longText('search_text')->nullable()->after('description');
            $table->fullText('search_text', 'products_search_text_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropFullText('products_search_text_fulltext');
            $table->dropColumn('search_text');
        });
    }
};
