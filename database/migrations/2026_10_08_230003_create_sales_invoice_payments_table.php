<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoice_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_invoice_id')
                ->constrained('sales_invoices')
                ->cascadeOnDelete();

            $table->date('paid_at');

            $table->unsignedBigInteger('amount');

            $table->string('currency', 3)->default('HUF');

            $table->string('payment_method')->nullable();

            $table->string('transaction_id')->nullable();

            $table->string('reference')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('paid_at');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_payments');
    }
};
