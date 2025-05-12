<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\IdocGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIdocJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Comanda care trebuie procesată pentru IDOC.
     *
     * @var Order
     */
    protected $order;

    /**
     * Tipul de IDOC care trebuie generat.
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, string $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(IdocGeneratorService $idocGenerator): void
    {
        try {
            Log::info('Începere procesare IDOC pentru comanda #' . $this->order->order_number . ', tip: ' . $this->type);
            
            if ($this->type === 'order') {
                $result = $idocGenerator->generatePlacedOrderDocuments($this->order);
                Log::info('IDOC de comandă generat cu succes pentru comanda #' . $this->order->order_number, $result);
            } 
            elseif ($this->type === 'delivery') {
                $result = $idocGenerator->generateDeliveredOrderDocuments($this->order);
                Log::info('IDOC de livrare generat cu succes pentru comanda #' . $this->order->order_number, $result);
            }
            else {
                Log::warning('Tip de IDOC necunoscut: ' . $this->type);
            }
        } catch (\Exception $e) {
            Log::error('Eroare la procesarea IDOC pentru comanda #' . $this->order->order_number . ': ' . $e->getMessage(), [
                'exception' => $e,
                'order_id' => $this->order->id,
                'type' => $this->type
            ]);
            
            throw $e;
        }
    }
}
