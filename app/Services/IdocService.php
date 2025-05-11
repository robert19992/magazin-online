<?php

namespace App\Services;

use App\Models\Order;
use App\Models\IdocMessage;
use Illuminate\Support\Str;

class IdocService
{
    /**
     * Generează și salvează un IDOC pentru o comandă
     */
    public function generateOrderIdoc(Order $order)
    {
        // Generăm un ID unic de corelație
        $correlationId = Str::uuid();
        
        // Construim structura IDOC pentru comandă
        $idocContent = [
            'EDI_DC40' => [
                'TABNAM' => 'EDI_DC40',
                'MANDT' => '100',
                'DOCNUM' => $order->order_number,
                'DOCREL' => '740',
                'STATUS' => '30',
                'DIRECT' => '2',
                'OUTMOD' => '2',
                'IDOCTYP' => 'ORDERS05',
                'MESTYP' => 'ORDERS',
                'SNDPOR' => 'WEBSHOP',
                'SNDPRT' => 'LS',
                'SNDPRN' => config('app.name'),
                'RCVPOR' => 'SAPB2B',
                'RCVPRT' => 'LS',
                'RCVPRN' => $order->supplier->organization->teccom_id
            ],
            'E1EDK01' => [
                'ACTION' => '09',
                'CURCY' => $order->currency,
                'WKURS' => '1.00000',
                'ZTERM' => '0001',
                'BSART' => $order->type === 'order' ? 'ZB2B' : 'ZRFQ',
                'BELNR' => $order->order_number
            ],
            'E1EDKA1' => [
                [
                    'PARVW' => 'AG', // Solicitant
                    'PARTN' => $order->customer->organization->teccom_id,
                    'NAME1' => $order->customer->organization->name,
                    'STRAS' => $order->shipping_address,
                    'ORT01' => $order->shipping_city,
                    'PSTLZ' => $order->shipping_postal_code,
                    'LAND1' => $order->shipping_country
                ],
                [
                    'PARVW' => 'LF', // Furnizor
                    'PARTN' => $order->supplier->organization->teccom_id
                ]
            ],
            'E1EDP01' => []
        ];

        // Adăugăm articolele comenzii
        foreach ($order->items as $item) {
            $idocContent['E1EDP01'][] = [
                'POSEX' => $item->id,
                'ACTION' => '009',
                'PSTYP' => '2',
                'MENGE' => $item->quantity,
                'PMENE' => 'PCE',
                'MATNR' => $item->product->code,
                'ARKTX' => $item->product->description,
                'WERKS' => $order->supplier->organization->teccom_id,
                'E1EDP19' => [
                    'QUALF' => '001',
                    'PRICE' => $item->unit_price,
                    'PEINH' => '1',
                    'CURCY' => $order->currency
                ]
            ];
        }

        // Salvăm mesajul IDOC
        $idocMessage = IdocMessage::create([
            'order_id' => $order->id,
            'type' => $order->type === 'order' ? 'ORDERS' : 'QUOTE',
            'direction' => 'outbound',
            'status' => 'pending',
            'content' => $idocContent,
            'correlation_id' => $correlationId
        ]);

        // În producție, aici am trimite IDOC-ul către sistemul ERP
        // Pentru simulare, vom marca mesajul ca trimis
        $idocMessage->update(['status' => 'sent']);

        return $idocMessage;
    }

    /**
     * Procesează un răspuns IDOC de la furnizor
     */
    public function processSupplierResponse($idocContent, $correlationId)
    {
        // Găsim mesajul original după correlation_id
        $originalMessage = IdocMessage::where('correlation_id', $correlationId)->first();
        if (!$originalMessage) {
            throw new \Exception('Mesaj IDOC original negăsit');
        }

        $order = $originalMessage->order;

        // Creăm mesajul de răspuns
        $responseMessage = IdocMessage::create([
            'order_id' => $order->id,
            'type' => 'ORDRSP',
            'direction' => 'inbound',
            'status' => 'received',
            'content' => $idocContent,
            'correlation_id' => $correlationId
        ]);

        // Procesăm răspunsul și actualizăm comanda
        $this->updateOrderFromResponse($order, $idocContent);

        return $responseMessage;
    }

    /**
     * Actualizează statusul comenzii pe baza răspunsului
     */
    private function updateOrderFromResponse(Order $order, array $idocContent)
    {
        // Extragem statusul general din IDOC
        $status = $idocContent['E1EDK01']['STATUS'] ?? null;

        switch ($status) {
            case 'CONFIRMED':
                $order->status = 'confirmed';
                break;
            case 'PARTIAL':
                $order->status = 'partial';
                break;
            case 'REJECTED':
                $order->status = 'rejected';
                break;
            default:
                $order->status = 'pending';
        }

        // Actualizăm cantitățile confirmate pentru fiecare articol
        foreach ($idocContent['E1EDP01'] ?? [] as $itemData) {
            $orderItem = $order->items()->where('id', $itemData['POSEX'])->first();
            if ($orderItem) {
                $orderItem->update([
                    'confirmed_quantity' => $itemData['MENGE'] ?? null,
                    'status' => $itemData['STATUS'] ?? 'pending',
                    'rejection_reason' => $itemData['REASON'] ?? null
                ]);
            }
        }

        $order->save();
    }
} 