<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\IdocXmlGeneratorService;
use Illuminate\Console\Command;

class TestIdocGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:idoc-generator 
                            {order_id? : ID-ul comenzii pentru care se generează IDOC-ul}
                            {--type=order : Tipul de IDOC: order (comandă) sau delivery (livrare)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testează generarea unui fișier IDOC XML pentru o comandă sau livrare';

    /**
     * Execute the console command.
     */
    public function handle(IdocXmlGeneratorService $idocGenerator)
    {
        $orderId = $this->argument('order_id');
        $type = $this->option('type');

        if (!in_array($type, ['order', 'delivery'])) {
            $this->error('Tipul IDOC trebuie să fie "order" sau "delivery"');
            return 1;
        }

        if (!$orderId) {
            // Dacă nu s-a specificat un ID de comandă, alegem prima comandă disponibilă
            $order = Order::with(['client', 'supplier', 'items.product'])->first();
            
            if (!$order) {
                $this->error('Nu există comenzi în baza de date!');
                return 1;
            }
        } else {
            // Încărcăm comanda specificată cu toate relațiile necesare
            $order = Order::with(['client', 'supplier', 'items.product'])->find($orderId);
            
            if (!$order) {
                $this->error("Comanda cu ID-ul {$orderId} nu a fost găsită!");
                return 1;
            }
        }

        $this->info("Generăm IDOC de " . ($type === 'order' ? 'comandă' : 'livrare') . " pentru comanda #{$order->id}");

        try {
            // Generăm IDOC-ul în funcție de tipul selectat
            if ($type === 'order') {
                $filePath = $idocGenerator->generateOrderIdoc($order);
            } else {
                $filePath = $idocGenerator->generateDeliveryIdoc($order);
            }
            
            $this->info("IDOC generat cu succes: " . $filePath);
            
            // Afișăm conținutul fișierului
            $this->line('');
            $this->line('Conținutul fișierului IDOC:');
            $this->line('-------------------------');
            $this->line(file_get_contents($filePath));
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Eroare la generarea IDOC: " . $e->getMessage());
            return 1;
        }
    }
}
