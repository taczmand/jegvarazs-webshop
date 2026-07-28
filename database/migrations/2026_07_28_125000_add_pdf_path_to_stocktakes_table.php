<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocktakes', function (Blueprint $table) {
            if (!Schema::hasColumn('stocktakes', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stocktakes', function (Blueprint $table) {
            if (Schema::hasColumn('stocktakes', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
        });
    }
};
