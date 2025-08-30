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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            // Verzió információ
            $table->string('version')->default('v1');

            // Alap kontakt adatok
            $table->string('name');
            $table->string('mothers_name');
            $table->string('place_of_birth');
            $table->date('date_of_birth');
            $table->string('id_number')->nullable(); // Személyi igazolvány szám vagy más azonosító
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('address_line')->nullable();

            // További rugalmas mezők
            $table->json('data')->nullable(); // a többi szerződés mező
            $table->string('pdf_path')->nullable(); // PDF fájl elérési útja
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('viewed_by')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
