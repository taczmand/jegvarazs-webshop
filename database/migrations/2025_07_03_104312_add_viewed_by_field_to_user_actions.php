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
        Schema::table('user_actions', function (Blueprint $table) {
            $table->foreignId('viewed_by')->nullable()->after('data')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->datetimes('viewed_at')->nullable()->after('viewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_actions', function (Blueprint $table) {
            $table->dropForeign(['viewed_by']);
            $table->dropColumn('viewed_by');
            $table->dropColumn('viewed_at');
        });
    }
};
