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
        Schema::create('worksheets', function (Blueprint $table) {
            $table->id();
            $table->string('work_name')->comment('Munka megnevezése');
            $table->enum('work_type', ['Karbantartás', 'Szerelés', 'Felmérés'])->default('Szerelés');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country');
            $table->string('zip_code');
            $table->string('city');
            $table->string('address_line');
            $table->text('description')->nullable();
            $table->text('worker_report')->nullable();
            $table->date('installation_date')->comment('Szerelés dátuma');
            $table->foreignId('worker_id')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');
            $table->enum('work_status', ['Folyamatban', 'Kész', 'Lezárva'])->default('Folyamatban');
            $table->json('data')->nullable(); // Mentett adatok JSON formátumban
            $table->enum('payment_method', ['cash', 'transfer']);
            $table->integer('payment_amount')->default(0);
            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->onDelete('no action')
                ->onUpdate('no action');
            $table->foreignId('created_by')
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
        Schema::dropIfExists('worksheets');
    }
};
