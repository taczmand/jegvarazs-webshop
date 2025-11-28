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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('form_name')->nullable()->after('form_id');
            $table->string('full_name')->nullable()->after('form_name');
            $table->string('campaign_name')->nullable()->after('full_name');
            $table->string('email')->nullable()->after('campaign_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('city')->nullable()->after('phone');
            $table->enum('status', ['new', 'contacted', 'converted'])->nullable()->after('city');
            $table->string('viewed_by')->nullable()->after('status');
            $table->dateTime('viewed_at')->nullable()->after('viewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['form_name', 'full_name', 'campaign_name', 'email', 'phone', 'city', 'status', 'viewed_by', 'viewed_at']);
        });
    }
};
