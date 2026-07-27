<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_transfers', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_transfers', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->cascadeOnUpdate();
            }
        });
    }
};
