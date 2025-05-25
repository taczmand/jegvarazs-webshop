<?php

namespace App\Console\Commands;

use App\Models\BasicData;
use Illuminate\Console\Command;

class SyncBasicData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:basicdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Frissíti az alapadatokat a konfigurációs fájl alapján';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $config = config('basicdata');

        foreach ($config as $key => $default) {
            $existing = BasicData::where('key', $key)->first();

            if (!$existing) {
                BasicData::create([
                    'key' => $key,
                    'value' => $default,
                ]);
                $this->info("Hozzáadva: {$key} => {$default}");
            } else {
                $this->line("Létezik: {$key} (érték: {$existing->value})");
            }
        }

        $this->info('BasicData szinkronizálás kész.');
    }
}
