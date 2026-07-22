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
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_notes', 'company_name')) {
                $table->string('company_name')->nullable()->after('partner_address_line');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_tax_number')) {
                $table->string('company_tax_number')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_country')) {
                $table->string('company_country', 2)->nullable()->after('company_tax_number');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_zip_code')) {
                $table->string('company_zip_code')->nullable()->after('company_country');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_city')) {
                $table->string('company_city')->nullable()->after('company_zip_code');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_address_line')) {
                $table->string('company_address_line')->nullable()->after('company_city');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_email')) {
                $table->string('company_email')->nullable()->after('company_address_line');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_phone')) {
                $table->string('company_phone')->nullable()->after('company_email');
            }
            if (!Schema::hasColumn('delivery_notes', 'company_bank_account')) {
                $table->string('company_bank_account')->nullable()->after('company_phone');
            }

            if (!Schema::hasColumn('delivery_notes', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('note');
            }

            if (!Schema::hasColumn('delivery_notes', 'stock_deducted_at')) {
                $table->dateTime('stock_deducted_at')->nullable()->after('pdf_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_notes', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
            if (Schema::hasColumn('delivery_notes', 'stock_deducted_at')) {
                $table->dropColumn('stock_deducted_at');
            }

            if (Schema::hasColumn('delivery_notes', 'company_bank_account')) {
                $table->dropColumn('company_bank_account');
            }
            if (Schema::hasColumn('delivery_notes', 'company_phone')) {
                $table->dropColumn('company_phone');
            }
            if (Schema::hasColumn('delivery_notes', 'company_email')) {
                $table->dropColumn('company_email');
            }
            if (Schema::hasColumn('delivery_notes', 'company_address_line')) {
                $table->dropColumn('company_address_line');
            }
            if (Schema::hasColumn('delivery_notes', 'company_city')) {
                $table->dropColumn('company_city');
            }
            if (Schema::hasColumn('delivery_notes', 'company_zip_code')) {
                $table->dropColumn('company_zip_code');
            }
            if (Schema::hasColumn('delivery_notes', 'company_country')) {
                $table->dropColumn('company_country');
            }
            if (Schema::hasColumn('delivery_notes', 'company_tax_number')) {
                $table->dropColumn('company_tax_number');
            }
            if (Schema::hasColumn('delivery_notes', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });
    }
};
