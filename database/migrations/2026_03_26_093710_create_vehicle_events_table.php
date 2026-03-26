<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('type');
            $table->date('event_date');
            $table->string('value')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'type', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_events');
    }
};
