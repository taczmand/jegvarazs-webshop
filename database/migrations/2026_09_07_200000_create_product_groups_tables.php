<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });

        Schema::create('product_group_product', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_group_id')
                ->constrained('product_groups')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            $table->unique(['product_group_id', 'product_id']);
            $table->index(['product_id']);
        });

        Schema::create('product_group_quantity_discounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_group_id')
                ->constrained('product_groups')
                ->onDelete('cascade');

            $table->unsignedInteger('base_quantity');
            $table->decimal('percent_per_step', 10, 2);
            $table->decimal('max_percent', 10, 2)->nullable();

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->unique(['product_group_id']);
            $table->index(['product_group_id', 'is_active'], 'pgqd_group_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_group_quantity_discounts');
        Schema::dropIfExists('product_group_product');
        Schema::dropIfExists('product_groups');
    }
};
