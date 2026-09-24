<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `appointments` MODIFY `appointment_type` ENUM('Karbantartás','Felmérés','Érdeklődés','Egyéb','Részletfizetés') NOT NULL DEFAULT 'Karbantartás'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `appointments` MODIFY `appointment_type` ENUM('Karbantartás','Felmérés','Érdeklődés','Egyéb') NOT NULL DEFAULT 'Karbantartás'");
    }
};
