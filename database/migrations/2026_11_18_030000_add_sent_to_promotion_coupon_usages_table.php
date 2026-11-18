<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_coupon_usages', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_coupon_usages', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });
    }
};
