<?php

namespace App\Console\Commands;

use App\Models\PostalCode;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportPostalCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:postalcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importálja az irányítószámokat és településeket egy CSV fájlból';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('app/iranyitoszamok.csv');

        if (!file_exists($path)) {
            $this->error('Fájl nem található: ' . $path);
            return;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error('Nem sikerült megnyitni a fájlt.');
            return;
        }

        // Fejléc átugrása
        $header = fgetcsv($handle);

        $count = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {

            // Ha teljesen üres a sor, leáll
            if (empty(array_filter($data))) {
                break;
            }

            // [0] = zip, [1] = city
            PostalCode::updateOrCreate(
                ['zip' => $data[0]],
                ['city' => $data[1]]
            );

            $count++;
        }

        fclose($handle);

        $this->info("Importálás kész: {$count} sor feldolgozva.");
    }
}
