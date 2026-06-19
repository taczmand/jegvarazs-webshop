<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM('Új','Szerződés','Nem vette fel','Csak érdeklődött','Felmérés','Átgondolja','Nem érdekli','Túl messze van') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM('Új','Nem vette fel','Csak érdeklődött','Felmérés','Átgondolja','Nem érdekli','Túl messze van') NULL");
    }
};
