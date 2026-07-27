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
        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnUpdate();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnUpdate();

            $table->string('document_number')->unique();
            $table->date('transferred_at')->nullable();
            $table->string('status')->default('draft');

            $table->text('note_before_items')->nullable();
            $table->text('note_after_items')->nullable();
            $table->text('note')->nullable();

            $table->string('pdf_path')->nullable();
            $table->timestamp('stock_moved_at')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });

        Schema::create('warehouse_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_transfer_id')->constrained('warehouse_transfers')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 14, 3)->default(0);
            $table->string('note')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->index(['warehouse_transfer_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfer_items');
        Schema::dropIfExists('warehouse_transfers');
    }
};
