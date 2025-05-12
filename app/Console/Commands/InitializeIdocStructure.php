<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InitializeIdocStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idoc:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inițializează structura de directoare și fișiere pentru sistemul IDOC';

    /**
     * Directoarele necesare pentru funcționarea sistemului IDOC
     */
    protected $directories = [
        'IDOC_client',
        'IDOC_furnizor',
        'documente_site/facturi',
        'documente_site/avize',
        'documente_site/comenzi',
    ];

    /**
     * Șabloanele necesare pentru generarea documentelor
     */
    protected $templates = [
        'documente_site/facturi/factura_exemplu.txt' => "FACTURA NR: [NUMAR_FACTURA]\nDATA: [DATA]\n\nFURNIZOR: [NUME_FURNIZOR]\nID FURNIZOR: [ID_FURNIZOR]\nCUI: [CUI_FURNIZOR]\nADRESA: [ADRESA_FURNIZOR]\n\nCLIENT: [NUME_CLIENT]\nID CLIENT: [ID_CLIENT] \nCUI: [CUI_CLIENT]\nADRESA: [ADRESA_CLIENT]\n\nCOMANDA NR: [NUMAR_COMANDA]\nAVIZ LIVRARE NR: [NUMAR_AVIZ]\n\nPRODUSE FACTURATE:\n------------------------------------------\nCOD PRODUS | DESCRIERE | CANTITATE | PRET | TOTAL\n------------------------------------------\n[COD_1]    | [DESC_1]  | [CANT_1]  | [PRET_1] | [TOTAL_1]\n[COD_2]    | [DESC_2]  | [CANT_2]  | [PRET_2] | [TOTAL_2]\n[COD_3]    | [DESC_3]  | [CANT_3]  | [PRET_3] | [TOTAL_3]\n------------------------------------------\n\nTOTAL FACTURA: [SUMA_TOTALA]\n\nFactura generata automat de sistemul de comenzi.\n",
        
        'documente_site/avize/aviz_livrare_exemplu.txt' => "AVIZ DE LIVRARE NR: [NUMAR_AVIZ]\nDATA: [DATA]\n\nFURNIZOR: [NUME_FURNIZOR]\nID FURNIZOR: [ID_FURNIZOR]\n\nCLIENT: [NUME_CLIENT]\nID CLIENT: [ID_CLIENT]\n\nCOMANDA NR: [NUMAR_COMANDA]\n\nPRODUSE LIVRATE:\n------------------------------------------\nCOD PRODUS | DESCRIERE | CANTITATE | PRET\n------------------------------------------\n[COD_1]    | [DESC_1]  | [CANT_1]  | [PRET_1]\n[COD_2]    | [DESC_2]  | [CANT_2]  | [PRET_2]\n[COD_3]    | [DESC_3]  | [CANT_3]  | [PRET_3]\n------------------------------------------\n\nTOTAL LIVRARE: [SUMA_TOTALA]\n\nAviz generat automat de sistemul de comenzi.\n",
        
        'documente_site/comenzi/confirmare_comanda_exemplu.txt' => "CONFIRMARE COMANDA NR: [NUMAR_COMANDA]\nDATA: [DATA]\n\nFURNIZOR: [NUME_FURNIZOR]\nID FURNIZOR: [ID_FURNIZOR]\n\nCLIENT: [NUME_CLIENT] \nID CLIENT: [ID_CLIENT]\n\nPRODUSE COMANDATE:\n------------------------------------------\nCOD PRODUS | DESCRIERE | CANTITATE | PRET\n------------------------------------------\n[COD_1]    | [DESC_1]  | [CANT_1]  | [PRET_1]\n[COD_2]    | [DESC_2]  | [CANT_2]  | [PRET_2]\n[COD_3]    | [DESC_3]  | [CANT_3]  | [PRET_3]\n------------------------------------------\n\nTOTAL COMANDA: [SUMA_TOTALA]\n\nConfirmare generata automat de sistemul de comenzi.\n",
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Inițializare structură directoare și fișiere IDOC...');

        // Creăm directoarele necesare
        foreach ($this->directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Directorul '$directory' a fost creat.");
            } else {
                $this->comment("Directorul '$directory' există deja.");
            }
        }

        // Creăm fișierele șablon dacă nu există
        foreach ($this->templates as $templatePath => $content) {
            if (!File::exists($templatePath)) {
                File::put($templatePath, $content);
                $this->info("Șablonul '$templatePath' a fost creat.");
            } else {
                $this->comment("Șablonul '$templatePath' există deja.");
            }
        }

        $this->info('Rulare migrare pentru structura bazei de date...');
        $this->call('migrate');

        $this->info('Structura IDOC a fost inițializată cu succes!');
    }
}
