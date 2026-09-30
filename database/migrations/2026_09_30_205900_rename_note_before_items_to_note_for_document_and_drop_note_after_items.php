<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'sales_invoices',
            'purchase_invoices',
            'delivery_notes',
            'goods_receipts',
            'warehouse_transfers',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'note_for_document')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->text('note_for_document')->nullable();
                });
            }

            if (Schema::hasColumn($table, 'note_before_items')) {
                DB::table($table)->whereNull('note_for_document')->update([
                    'note_for_document' => DB::raw('note_before_items'),
                ]);
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'note_before_items')) {
                    $t->dropColumn('note_before_items');
                }
                if (Schema::hasColumn($table, 'note_after_items')) {
                    $t->dropColumn('note_after_items');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'sales_invoices',
            'purchase_invoices',
            'delivery_notes',
            'goods_receipts',
            'warehouse_transfers',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'note_before_items')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->text('note_before_items')->nullable();
                });
            }

            if (!Schema::hasColumn($table, 'note_after_items')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->text('note_after_items')->nullable();
                });
            }

            if (Schema::hasColumn($table, 'note_for_document')) {
                DB::table($table)->whereNull('note_before_items')->update([
                    'note_before_items' => DB::raw('note_for_document'),
                ]);

                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->dropColumn('note_for_document');
                });
            }
        }
    }
};
