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
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->string('tax_number')->nullable()->after('client_name');
        });

        // Meglévő clients.tax_number adatok átmásolása az ügyfél
        // alapértelmezett (vagy első) címéhez.
        foreach (DB::table('clients')->whereNotNull('tax_number')->where('tax_number', '!=', '')->get(['id', 'tax_number']) as $client) {
            $addressId = DB::table('client_addresses')
                ->where('client_id', $client->id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->value('id');

            if ($addressId) {
                DB::table('client_addresses')
                    ->where('id', $addressId)
                    ->update(['tax_number' => $client->tax_number]);
            }
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('tax_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('tax_number')->nullable()->after('id_number');
        });

        Schema::table('client_addresses', function (Blueprint $table) {
            $table->dropColumn('tax_number');
        });
    }
};
