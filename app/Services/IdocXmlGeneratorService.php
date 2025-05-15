<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class IdocXmlGeneratorService
{
    /**
     * Generează un fișier IDOC XML pentru o comandă și îl salvează în folderul specificat
     *
     * @param Order $order Comanda pentru care se generează IDOC-ul
     * @return string Calea către fișierul generat
     */
    public function generateOrderIdoc(Order $order)
    {
        // Ne asigurăm că folderul există
        $idocFolder = base_path('IDOC_client');
        if (!File::exists($idocFolder)) {
            File::makeDirectory($idocFolder, 0755, true);
        }

        // Generăm un nume unic pentru fișier
        $fileName = 'IDOC_ORDER_' . $order->id . '_' . date('YmdHis') . '_' . Str::random(5) . '.xml';
        $filePath = $idocFolder . '/' . $fileName;

        // Construim conținutul XML pentru IDOC
        $xml = $this->buildOrderXml($order);

        // Salvăm XML-ul în fișier
        File::put($filePath, $xml);

        return $filePath;
    }

    /**
     * Generează un fișier IDOC XML pentru o comandă livrată și îl salvează în folderul specificat
     *
     * @param Order $order Comanda pentru care se generează IDOC-ul de livrare
     * @return string Calea către fișierul generat
     */
    public function generateDeliveryIdoc(Order $order)
    {
        // Ne asigurăm că folderul există
        $idocFolder = base_path('IDOC_furnizor');
        if (!File::exists($idocFolder)) {
            File::makeDirectory($idocFolder, 0755, true);
        }

        // Generăm un nume unic pentru fișier
        $fileName = 'IDOC_DELIVERY_' . $order->id . '_' . date('YmdHis') . '_' . Str::random(5) . '.xml';
        $filePath = $idocFolder . '/' . $fileName;

        // Construim conținutul XML pentru IDOC
        $xml = $this->buildDeliveryXml($order);

        // Salvăm XML-ul în fișier
        File::put($filePath, $xml);

        return $filePath;
    }

    /**
     * Construiește conținutul XML pentru un IDOC de comandă
     *
     * @param Order $order Comanda pentru care se generează XML-ul
     * @return string Conținutul XML
     */
    private function buildOrderXml(Order $order)
    {
        // Data pentru IDOC
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $idocNumber = 'IDOC' . $order->id . date('YmdHis');

        // Începem construirea XML-ului
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<IDOC>' . PHP_EOL;
        
        // Adăugăm antetul IDOC
        $xml .= '  <IDOC_HEADER>' . PHP_EOL;
        $xml .= '    <IDOC_NUMBER>' . $idocNumber . '</IDOC_NUMBER>' . PHP_EOL;
        $xml .= '    <IDOC_TYPE>ORDERS</IDOC_TYPE>' . PHP_EOL;
        $xml .= '    <CREATED_DATE>' . $currentDate . '</CREATED_DATE>' . PHP_EOL;
        $xml .= '    <CREATED_TIME>' . $currentTime . '</CREATED_TIME>' . PHP_EOL;
        $xml .= '  </IDOC_HEADER>' . PHP_EOL;

        // Adăugăm antetul comenzii
        $xml .= '  <E1EDK01>' . PHP_EOL;
        $xml .= '    <ORDER_NUMBER>' . $order->id . '</ORDER_NUMBER>' . PHP_EOL;
        $xml .= '    <ORDER_DATE>' . $order->created_at->format('Y-m-d') . '</ORDER_DATE>' . PHP_EOL;
        $xml .= '    <CURRENCY>RON</CURRENCY>' . PHP_EOL;
        $xml .= '    <TOTAL_AMOUNT>' . $order->total_amount . '</TOTAL_AMOUNT>' . PHP_EOL;
        $xml .= '  </E1EDK01>' . PHP_EOL;

        // Adăugăm informații despre client și furnizor
        $xml .= '  <E1EDKA1>' . PHP_EOL;
        $xml .= '    <CLIENT_ID>' . $order->client_id . '</CLIENT_ID>' . PHP_EOL;
        $xml .= '    <CLIENT_NAME>' . ($order->client->company_name ?? 'N/A') . '</CLIENT_NAME>' . PHP_EOL;
        $xml .= '    <SUPPLIER_ID>' . $order->supplier_id . '</SUPPLIER_ID>' . PHP_EOL;
        $xml .= '    <SUPPLIER_NAME>' . ($order->supplier->company_name ?? 'N/A') . '</SUPPLIER_NAME>' . PHP_EOL;
        $xml .= '  </E1EDKA1>' . PHP_EOL;

        // Adăugăm articolele comenzii
        $xml .= '  <E1EDP01>' . PHP_EOL;
        
        foreach ($order->items as $index => $item) {
            $xml .= '    <ITEM>' . PHP_EOL;
            $xml .= '      <ITEM_NUMBER>' . ($index + 1) . '</ITEM_NUMBER>' . PHP_EOL;
            $xml .= '      <PRODUCT_ID>' . $item->product_id . '</PRODUCT_ID>' . PHP_EOL;
            $xml .= '      <PRODUCT_CODE>' . ($item->product->cod_produs ?? 'N/A') . '</PRODUCT_CODE>' . PHP_EOL;
            $xml .= '      <DESCRIPTION>' . ($item->product->description ?? 'N/A') . '</DESCRIPTION>' . PHP_EOL;
            $xml .= '      <QUANTITY>' . $item->quantity . '</QUANTITY>' . PHP_EOL;
            $xml .= '      <UNIT_PRICE>' . $item->price . '</UNIT_PRICE>' . PHP_EOL;
            $xml .= '      <TOTAL_PRICE>' . ($item->quantity * $item->price) . '</TOTAL_PRICE>' . PHP_EOL;
            $xml .= '    </ITEM>' . PHP_EOL;
        }
        
        $xml .= '  </E1EDP01>' . PHP_EOL;

        // Închidem IDOC
        $xml .= '</IDOC>';

        return $xml;
    }

    /**
     * Construiește conținutul XML pentru un IDOC de livrare
     *
     * @param Order $order Comanda pentru care se generează XML-ul de livrare
     * @return string Conținutul XML
     */
    private function buildDeliveryXml(Order $order)
    {
        // Data pentru IDOC
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $idocNumber = 'IDOC_DEL' . $order->id . date('YmdHis');

        // Începem construirea XML-ului
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<IDOC>' . PHP_EOL;
        
        // Adăugăm antetul IDOC
        $xml .= '  <IDOC_HEADER>' . PHP_EOL;
        $xml .= '    <IDOC_NUMBER>' . $idocNumber . '</IDOC_NUMBER>' . PHP_EOL;
        $xml .= '    <IDOC_TYPE>DESADV</IDOC_TYPE>' . PHP_EOL;
        $xml .= '    <CREATED_DATE>' . $currentDate . '</CREATED_DATE>' . PHP_EOL;
        $xml .= '    <CREATED_TIME>' . $currentTime . '</CREATED_TIME>' . PHP_EOL;
        $xml .= '  </IDOC_HEADER>' . PHP_EOL;

        // Adăugăm antetul livrării
        $xml .= '  <E1EDL20>' . PHP_EOL;
        $xml .= '    <DELIVERY_NUMBER>' . $order->id . '</DELIVERY_NUMBER>' . PHP_EOL;
        $xml .= '    <ORDER_NUMBER>' . $order->order_number . '</ORDER_NUMBER>' . PHP_EOL;
        $xml .= '    <ORDER_DATE>' . $order->created_at->format('Y-m-d') . '</ORDER_DATE>' . PHP_EOL;
        $xml .= '    <DELIVERY_DATE>' . date('Y-m-d') . '</DELIVERY_DATE>' . PHP_EOL;
        $xml .= '    <TOTAL_AMOUNT>' . $order->total_amount . '</TOTAL_AMOUNT>' . PHP_EOL;
        $xml .= '    <CURRENCY>RON</CURRENCY>' . PHP_EOL;
        $xml .= '  </E1EDL20>' . PHP_EOL;

        // Adăugăm informații despre client și furnizor
        $xml .= '  <E1EDKA1>' . PHP_EOL;
        $xml .= '    <CLIENT_ID>' . $order->client_id . '</CLIENT_ID>' . PHP_EOL;
        $xml .= '    <CLIENT_NAME>' . ($order->client->company_name ?? 'N/A') . '</CLIENT_NAME>' . PHP_EOL;
        $xml .= '    <SUPPLIER_ID>' . $order->supplier_id . '</SUPPLIER_ID>' . PHP_EOL;
        $xml .= '    <SUPPLIER_NAME>' . ($order->supplier->company_name ?? 'N/A') . '</SUPPLIER_NAME>' . PHP_EOL;
        $xml .= '  </E1EDKA1>' . PHP_EOL;

        // Adăugăm articolele livrate
        $xml .= '  <E1EDL24>' . PHP_EOL;
        
        foreach ($order->items as $index => $item) {
            $xml .= '    <ITEM>' . PHP_EOL;
            $xml .= '      <ITEM_NUMBER>' . ($index + 1) . '</ITEM_NUMBER>' . PHP_EOL;
            $xml .= '      <PRODUCT_ID>' . $item->product_id . '</PRODUCT_ID>' . PHP_EOL;
            $xml .= '      <PRODUCT_CODE>' . ($item->product->cod_produs ?? 'N/A') . '</PRODUCT_CODE>' . PHP_EOL;
            $xml .= '      <DESCRIPTION>' . ($item->product->description ?? 'N/A') . '</DESCRIPTION>' . PHP_EOL;
            $xml .= '      <QUANTITY>' . $item->quantity . '</QUANTITY>' . PHP_EOL;
            $xml .= '      <UNIT_PRICE>' . $item->price . '</UNIT_PRICE>' . PHP_EOL;
            $xml .= '      <TOTAL_PRICE>' . ($item->quantity * $item->price) . '</TOTAL_PRICE>' . PHP_EOL;
            $xml .= '    </ITEM>' . PHP_EOL;
        }
        
        $xml .= '  </E1EDL24>' . PHP_EOL;

        // Închidem IDOC
        $xml .= '</IDOC>';

        return $xml;
    }
} 