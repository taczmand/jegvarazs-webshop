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
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->id();

            $table->string('related_type')->nullable();
            $table->string('related_value')->nullable();

            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();

            $table->integer('amount');

            $table->string('received_from_name')->nullable();
            $table->date('received_date')->nullable();

            $table->string('status')->default('pending');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->dateTime('acknowledged_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->index(['related_type', 'related_value']);
            $table->index(['status', 'acknowledged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_receipts');
    }
};
