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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Vásárló kapcsolattartó adatai
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('contact_email');
            $table->string('contact_phone');

            // Számlázási adatok
            $table->string('billing_name');
            $table->string('billing_country');
            $table->string('billing_postal_code');
            $table->string('billing_city');
            $table->string('billing_address_line');
            $table->string('billing_tax_number')->nullable();

            // Szállítási adatok
            $table->string('shipping_name');
            $table->string('shipping_country');
            $table->string('shipping_postal_code');
            $table->string('shipping_city');
            $table->string('shipping_address_line');

            $table->string('payment_method');

            // Egyéb mezők
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'canceled', 'paid', 'payment_failed'])->default('pending');
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
        Schema::dropIfExists('orders');
    }
};
