<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('automated_emails', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('automated_emails', function (Blueprint $table) {
            $table->dropColumn('payload');
        });
    }
};
