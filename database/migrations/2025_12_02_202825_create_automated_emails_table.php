<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('automated_emails', function (Blueprint $table) {
            $table->id();

            // email template ID vagy valamilyen sablon azonosító
            $table->string('email_template')->nullable();

            // lehet egyedi email cím
            $table->string('email_address');

            // gyakoriság
            $table->enum('frequency_unit', ['naponta', 'hetente', 'havonta', 'évente']);
            $table->integer('frequency_interval')->default(1);

            // utolsó küldés időpontja
            $table->dateTime('last_sent_at')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automated_emails');
    }
};
