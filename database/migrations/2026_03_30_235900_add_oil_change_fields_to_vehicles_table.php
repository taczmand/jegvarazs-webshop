<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedInteger('current_odometer')->nullable()->after('note');
            $table->unsignedInteger('last_oil_change_odometer')->nullable()->after('current_odometer');
            $table->unsignedInteger('oil_change_interval')->default(12000)->after('last_oil_change_odometer');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['current_odometer', 'last_oil_change_odometer', 'oil_change_interval']);
        });
    }
};
