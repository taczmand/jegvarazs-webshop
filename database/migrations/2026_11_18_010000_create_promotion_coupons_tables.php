<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')
                ->constrained('promotions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('code')->unique();
            $table->timestamps();

            $table->index(['promotion_id']);
        });

        Schema::create('promotion_coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_coupon_id')
                ->constrained('promotion_coupons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('email');
            $table->timestamps();

            $table->index(['promotion_coupon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_coupon_usages');
        Schema::dropIfExists('promotion_coupons');
    }
};
