<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_offer_id')->constrained('partner_offers')->cascadeOnDelete();
            $table->string('type', 20);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('title', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('gross_price', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_offer_items');
    }
};
