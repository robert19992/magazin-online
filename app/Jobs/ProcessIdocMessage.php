<?php

namespace App\Jobs;

use App\Models\IdocMessage;
use App\Services\IdocService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIdocMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idocMessage;
    public $tries = 3; // Numărul de încercări în caz de eșec
    public $timeout = 120; // Timeout în secunde

    /**
     * Create a new job instance.
     */
    public function __construct(IdocMessage $idocMessage)
    {
        $this->idocMessage = $idocMessage;
    }

    /**
     * Execute the job.
     */
    public function handle(IdocService $idocService)
    {
        try {
            Log::info('Începe procesarea IDOC', [
                'message_id' => $this->idocMessage->id,
                'type' => $this->idocMessage->type,
                'correlation_id' => $this->idocMessage->correlation_id
            ]);

            // În funcție de tipul mesajului, procesăm diferit
            switch ($this->idocMessage->type) {
                case 'ORDERS':
                    $this->processOrderMessage($idocService);
                    break;
                case 'ORDRSP':
                    $this->processResponseMessage($idocService);
                    break;
                case 'DESADV':
                    $this->processDeliveryMessage($idocService);
                    break;
                case 'INVOIC':
                    $this->processInvoiceMessage($idocService);
                    break;
                default:
                    throw new \Exception('Tip de mesaj IDOC necunoscut: ' . $this->idocMessage->type);
            }

            Log::info('IDOC procesat cu succes', [
                'message_id' => $this->idocMessage->id
            ]);

        } catch (\Exception $e) {
            Log::error('Eroare la procesarea IDOC', [
                'message_id' => $this->idocMessage->id,
                'error' => $e->getMessage()
            ]);

            $this->idocMessage->update([
                'status' => 'error',
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Procesează un mesaj de comandă
     */
    protected function processOrderMessage(IdocService $idocService)
    {
        // Simulăm trimiterea către ERP
        sleep(2); // Simulăm o întârziere de rețea

        // Actualizăm statusul
        $this->idocMessage->update([
            'status' => 'sent'
        ]);

        // Simulăm un răspuns automat de la ERP
        $responseContent = [
            'E1EDK01' => [
                'STATUS' => 'CONFIRMED',
                'BELNR' => $this->idocMessage->order->order_number
            ],
            'E1EDP01' => []
        ];

        // Adăugăm confirmarea pentru fiecare articol
        foreach ($this->idocMessage->order->items as $item) {
            $responseContent['E1EDP01'][] = [
                'POSEX' => $item->id,
                'STATUS' => 'confirmed',
                'MENGE' => $item->quantity
            ];
        }

        // Procesăm răspunsul simulat
        $idocService->processSupplierResponse($responseContent, $this->idocMessage->correlation_id);
    }

    /**
     * Procesează un mesaj de răspuns
     */
    protected function processResponseMessage(IdocService $idocService)
    {
        // Actualizăm statusul comenzii pe baza răspunsului
        $idocService->processSupplierResponse(
            $this->idocMessage->content,
            $this->idocMessage->correlation_id
        );

        $this->idocMessage->update(['status' => 'processed']);
    }

    /**
     * Procesează un mesaj de livrare
     */
    protected function processDeliveryMessage(IdocService $idocService)
    {
        $order = $this->idocMessage->order;
        
        // Actualizăm statusul comenzii
        $order->update([
            'status' => 'shipped',
            'estimated_delivery_date' => now()->addDays(2)
        ]);

        $this->idocMessage->update(['status' => 'processed']);
    }

    /**
     * Procesează o factură
     */
    protected function processInvoiceMessage(IdocService $idocService)
    {
        $order = $this->idocMessage->order;
        
        // Actualizăm statusul comenzii
        $order->update([
            'status' => 'invoiced',
            'invoice_number' => $this->idocMessage->content['E1EDK01']['BELNR'] ?? null,
            'invoice_date' => now()
        ]);

        $this->idocMessage->update(['status' => 'processed']);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Job de procesare IDOC eșuat', [
            'message_id' => $this->idocMessage->id,
            'error' => $exception->getMessage()
        ]);

        // Notificăm administratorii despre eșec
        // În producție, aici am putea trimite un email sau o notificare
    }
} 