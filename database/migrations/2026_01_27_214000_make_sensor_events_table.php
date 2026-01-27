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
        if (!Schema::hasTable('sensor_events')) {
            Schema::create('sensor_events', function (Blueprint $table) {
                $table->id();
                $table->string('device_id', 64);
                $table->string('event', 50)->nullable();
                $table->string('sensor', 50)->nullable();
                $table->string('value')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

                $table->index(['device_id', 'occurred_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_events');
    }
};
