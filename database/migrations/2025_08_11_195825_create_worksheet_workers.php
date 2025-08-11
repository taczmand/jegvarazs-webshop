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
        Schema::create('worksheet_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')
                ->constrained('worksheets')
                ->onDelete('no action')
                ->onUpdate('no action');
            $table->foreignId('worker_id')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheet_workers');
    }
};
