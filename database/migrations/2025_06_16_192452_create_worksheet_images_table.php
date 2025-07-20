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
        Schema::create('worksheet_images', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->enum('image_type', ['Adattábla', 'Telepítési tanúsítvány', 'Szerelés', 'Helyszíni felmérés', 'Számla'])->default('Szerelés');
            $table->foreignId('worksheet_id')
                ->constrained('worksheets')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheet_item_images');
    }
};
