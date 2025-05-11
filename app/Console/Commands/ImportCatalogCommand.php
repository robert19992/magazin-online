<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ImportCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:import {file?} {--supplier_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importă catalogul de produse din fișier CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obține supplier_id din opțiune sau caută primul furnizor
        $supplierId = $this->option('supplier_id');
        if (!$supplierId) {
            $supplier = User::where('account_type', 'supplier')->first();
            if (!$supplier) {
                $this->error('Nu există niciun furnizor în sistem. Creați un furnizor sau specificați supplier_id.');
                return 1;
            }
            $supplierId = $supplier->id;
        }

        $file = $this->argument('file') ?? 'catalog_cat.csv';
        $path = storage_path("app/catalogs/$file");

        if (!file_exists($path)) {
            $this->error("Fișierul $file nu există în storage/app/catalogs/");
            return 1;
        }

        $this->info('Începe importul catalogului...');
        $header = true;
        $imported = 0;
        $errors = 0;

        if (($handle = fopen($path, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if ($header) {
                    $header = false;
                    continue;
                }

                // Curăță spațiile extra din date
                $data = array_map('trim', $data);

                try {
                    // Convertește data
                    $date = Carbon::parse($data[6])->format('Y-m-d');

                    // Actualizează sau creează produsul
                    Product::updateOrCreate(
                        ['code' => $data[0]], // Caută după cod
                        [
                            'description' => $data[1],
                            'manufacturer' => $data[2],
                            'weight' => (float)$data[3],
                            'price' => (float)$data[4],
                            'stock' => (int)$data[5],
                            'introduction_date' => $date,
                            'supplier_id' => $supplierId
                        ]
                    );

                    $imported++;
                    $this->info("Produs importat cu succes: {$data[0]}");
                } catch (\Exception $e) {
                    $this->error("Eroare la importul produsului {$data[0]}: " . $e->getMessage());
                    $errors++;
                }
            }
            fclose($handle);
        }

        $this->info("Import finalizat: $imported produse importate cu succes, $errors erori.");
        return 0;
    }
}
