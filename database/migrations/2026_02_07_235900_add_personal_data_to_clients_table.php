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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('mothers_name')->nullable()->after('name');
            $table->string('place_of_birth')->nullable()->after('mothers_name');
            $table->date('date_of_birth')->nullable()->after('place_of_birth');
            $table->string('id_number')->nullable()->after('date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['mothers_name', 'place_of_birth', 'date_of_birth', 'id_number']);
        });
    }
};
