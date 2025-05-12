<?php

namespace App\Services;

use App\Models\Order;
use App\Models\IdocMessage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IdocGeneratorService
{
    /**
     * Directoarele pentru stocarea fișierelor generate
     */
    protected $directories;
    
    /**
     * Șabloanele pentru diferite tipuri de documente
     */
    protected $templates;
    
    /**
     * Formatul SAP IDOC
     */
    protected $sapFormat;
    
    /**
     * Configurările pentru logging
     */
    protected $loggingConfig;
    
    /**
     * Inițializează serviciul cu configurările necesare
     */
    public function __construct()
    {
        $this->directories = Config::get('idoc.directories', [
            'idoc_client' => 'IDOC_client',
            'idoc_furnizor' => 'IDOC_furnizor',
            'facturi' => 'documente_site/facturi',
            'avize' => 'documente_site/avize',
            'comenzi' => 'documente_site/comenzi',
        ]);
        
        $this->templates = Config::get('idoc.templates', [
            'factura' => 'documente_site/facturi/factura_exemplu.txt',
            'aviz' => 'documente_site/avize/aviz_livrare_exemplu.txt',
            'confirmare' => 'documente_site/comenzi/confirmare_comanda_exemplu.txt',
        ]);
        
        $this->sapFormat = Config::get('idoc.sap_format', [
            'order_message_type' => 'ORDERS',
            'delivery_message_type' => 'DESADV',
            'segment_prefix' => 'E1',
            'header_segment' => 'EDK01',
            'partner_segment' => 'EDKA1',
            'item_segment' => 'EDP01',
            'summary_segment' => 'EDS01',
            'control_record_type' => 'EDI_DC40',
        ]);
        
        $this->loggingConfig = Config::get('idoc.logging', [
            'enabled' => true,
            'channel' => 'idoc',
        ]);
    }

    /**
     * Generează IDOC și confirmarea pentru o comandă nou plasată
     *
     * @param Order $order Comanda pentru care se generează IDOC-ul
     * @return array Căile către fișierele generate
     */
    public function generatePlacedOrderDocuments(Order $order): array
    {
        $this->logInfo('Generare documente pentru comanda plasată #' . $order->order_number);
        
        // Verificăm dacă directoarele există
        $this->ensureDirectoriesExist();

        // Generăm numele unice pentru fișiere
        $idocFileName = $this->generateUniqueFileName('IDOC_CMD_' . $order->order_number);
        $confirmationFileName = $this->generateUniqueFileName('CONFIRMARE_' . $order->order_number);

        // Generăm conținutul pentru IDOC și confirmarea de comandă
        $idocContent = $this->generateOrderIdocContent($order);
        $confirmationContent = $this->generateConfirmationContent($order);

        // Salvăm fișierele
        $idocPath = $this->saveFile($this->directories['idoc_furnizor'] . '/' . $idocFileName, $idocContent);
        $confirmationPath = $this->saveFile($this->directories['comenzi'] . '/' . $confirmationFileName, $confirmationContent);

        // Înregistrăm documentele în baza de date
        $this->registerDocument($order, 'confirmation', $confirmationPath);
        
        // Înregistrăm mesajul IDOC în baza de date
        $this->createIdocMessage(
            $order, 
            IdocMessage::TYPE_ORDER,
            IdocMessage::DIRECTION_CLIENT_TO_SUPPLIER,
            $idocPath,
            $idocContent
        );
        
        // Marcăm comanda ca având IDOC-ul generat
        $order->update([
            'idoc_order_generated' => true,
            'idoc_order_generated_at' => now()
        ]);
        
        $this->logInfo('Documente generate cu succes pentru comanda #' . $order->order_number, [
            'idoc_path' => $idocPath,
            'confirmation_path' => $confirmationPath
        ]);

        return [
            'idoc' => $idocPath,
            'confirmation' => $confirmationPath
        ];
    }

    /**
     * Generează IDOC, aviz de livrare și factură pentru o comandă livrată
     *
     * @param Order $order Comanda pentru care se generează documentele
     * @return array Căile către fișierele generate
     */
    public function generateDeliveredOrderDocuments(Order $order): array
    {
        $this->logInfo('Generare documente pentru comanda livrată #' . $order->order_number);
        
        // Verificăm dacă directoarele există
        $this->ensureDirectoriesExist();

        // Generăm numele unice pentru fișiere
        $idocFileName = $this->generateUniqueFileName('IDOC_LIV_' . $order->order_number);
        $avizFileName = $this->generateUniqueFileName('AVIZ_' . $order->order_number);
        $facturaFileName = $this->generateUniqueFileName('FACTURA_' . $order->order_number);

        // Generăm conținutul pentru fiecare document
        $idocContent = $this->generateDeliveryIdocContent($order);
        $avizContent = $this->generateDeliveryNoteContent($order);
        $facturaContent = $this->generateInvoiceContent($order);

        // Salvăm fișierele
        $idocPath = $this->saveFile($this->directories['idoc_client'] . '/' . $idocFileName, $idocContent);
        $avizPath = $this->saveFile($this->directories['avize'] . '/' . $avizFileName, $avizContent);
        $facturaPath = $this->saveFile($this->directories['facturi'] . '/' . $facturaFileName, $facturaContent);

        // Înregistrăm documentele în baza de date
        $this->registerDocument($order, 'delivery_note', $avizPath);
        $this->registerDocument($order, 'invoice', $facturaPath);
        
        // Înregistrăm mesajul IDOC în baza de date
        $this->createIdocMessage(
            $order, 
            IdocMessage::TYPE_DELIVERY,
            IdocMessage::DIRECTION_SUPPLIER_TO_CLIENT,
            $idocPath,
            $idocContent
        );
        
        // Marcăm comanda ca având IDOC-ul de livrare generat
        $order->update([
            'idoc_delivery_generated' => true,
            'idoc_delivery_generated_at' => now()
        ]);
        
        $this->logInfo('Documente de livrare generate cu succes pentru comanda #' . $order->order_number, [
            'idoc_path' => $idocPath,
            'aviz_path' => $avizPath,
            'factura_path' => $facturaPath
        ]);

        return [
            'idoc' => $idocPath,
            'aviz' => $avizPath,
            'factura' => $facturaPath
        ];
    }

    /**
     * Generează conținutul IDOC pentru o comandă plasată
     *
     * @param Order $order Comanda
     * @return string Conținutul IDOC
     */
    private function generateOrderIdocContent(Order $order): string
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $idocNumber = 'IDOC' . $timestamp . $order->order_number;
        
        $content = "EDI_DC40 " . str_pad($idocNumber, 16, '0', STR_PAD_LEFT) . " " . $timestamp . "\n";
        $content .= "ORDERS                         " . $order->order_number . "\n";
        $content .= "E1EDK01 " . str_pad("BELNR", 20) . $order->order_number . "\n";
        $content .= "E1EDK01 " . str_pad("STATUS", 20) . "10" . "\n";  // Status 10 = Order Created
        $content .= "E1EDK01 " . str_pad("DATUM", 20) . Carbon::now()->format('Ymd') . "\n";
        $content .= "E1EDK01 " . str_pad("UZEIT", 20) . Carbon::now()->format('His') . "\n";
        
        // Header pentru furnizor
        $content .= "E1EDKA1 " . str_pad("PARVW", 20) . "LF" . "\n";  // LF = Supplier
        $content .= "E1EDKA1 " . str_pad("PARTN", 20) . $order->supplier->connect_id . "\n";
        $content .= "E1EDKA1 " . str_pad("NAME1", 20) . $order->supplier->organization->name . "\n";
        
        // Header pentru client
        $content .= "E1EDKA1 " . str_pad("PARVW", 20) . "AG" . "\n";  // AG = Customer
        $content .= "E1EDKA1 " . str_pad("PARTN", 20) . $order->client->connect_id . "\n";
        $content .= "E1EDKA1 " . str_pad("NAME1", 20) . $order->client->organization->name . "\n";
        
        // Detalii pentru linia de comandă
        $lineNumber = 10;
        foreach ($order->items as $item) {
            $content .= "E1EDP01 " . str_pad("POSEX", 20) . str_pad($lineNumber, 6, '0', STR_PAD_LEFT) . "\n";
            $content .= "E1EDP01 " . str_pad("MENGE", 20) . str_pad($item->quantity, 15, ' ', STR_PAD_LEFT) . "\n";
            $content .= "E1EDP01 " . str_pad("ARKTX", 20) . $item->product->description . "\n";
            $content .= "E1EDP01 " . str_pad("NETPR", 20) . str_pad(number_format($item->unit_price, 2, '.', ''), 15, ' ', STR_PAD_LEFT) . "\n";
            $content .= "E1EDP01 " . str_pad("VPREI", 20) . str_pad(number_format($item->quantity * $item->unit_price, 2, '.', ''), 15, ' ', STR_PAD_LEFT) . "\n";
            $lineNumber += 10;
        }
        
        // Sumar
        $content .= "E1EDS01 " . str_pad("SUMME", 20) . str_pad(number_format($order->total_amount, 2, '.', ''), 15, ' ', STR_PAD_LEFT) . "\n";
        
        return $content;
    }
    
    /**
     * Generează conținutul IDOC pentru o comandă livrată
     *
     * @param Order $order Comanda
     * @return string Conținutul IDOC
     */
    private function generateDeliveryIdocContent(Order $order): string
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $idocNumber = 'IDOC' . $timestamp . $order->order_number;
        
        $content = "EDI_DC40 " . str_pad($idocNumber, 16, '0', STR_PAD_LEFT) . " " . $timestamp . "\n";
        $content .= "DESADV                         " . $order->order_number . "\n";
        $content .= "E1EDK01 " . str_pad("BELNR", 20) . $order->order_number . "\n";
        $content .= "E1EDK01 " . str_pad("STATUS", 20) . "30" . "\n";  // Status 30 = Delivery
        $content .= "E1EDK01 " . str_pad("DATUM", 20) . Carbon::now()->format('Ymd') . "\n";
        $content .= "E1EDK01 " . str_pad("UZEIT", 20) . Carbon::now()->format('His') . "\n";
        
        // Header pentru furnizor
        $content .= "E1EDKA1 " . str_pad("PARVW", 20) . "LF" . "\n";  // LF = Supplier
        $content .= "E1EDKA1 " . str_pad("PARTN", 20) . $order->supplier->connect_id . "\n";
        $content .= "E1EDKA1 " . str_pad("NAME1", 20) . $order->supplier->organization->name . "\n";
        
        // Header pentru client
        $content .= "E1EDKA1 " . str_pad("PARVW", 20) . "AG" . "\n";  // AG = Customer
        $content .= "E1EDKA1 " . str_pad("PARTN", 20) . $order->client->connect_id . "\n";
        $content .= "E1EDKA1 " . str_pad("NAME1", 20) . $order->client->organization->name . "\n";
        
        // Detalii pentru linia de comandă
        $lineNumber = 10;
        foreach ($order->items as $item) {
            $content .= "E1EDP01 " . str_pad("POSEX", 20) . str_pad($lineNumber, 6, '0', STR_PAD_LEFT) . "\n";
            $content .= "E1EDP01 " . str_pad("MENGE", 20) . str_pad($item->quantity, 15, ' ', STR_PAD_LEFT) . "\n";
            $content .= "E1EDP01 " . str_pad("ARKTX", 20) . $item->product->description . "\n";
            $content .= "E1EDP01 " . str_pad("NETPR", 20) . str_pad(number_format($item->unit_price, 2, '.', ''), 15, ' ', STR_PAD_LEFT) . "\n";
            $content .= "E1EDP01 " . str_pad("VPREI", 20) . str_pad(number_format($item->quantity * $item->unit_price, 2, '.', ''), 15, ' ', STR_PAD_LEFT) . "\n";
            $lineNumber += 10;
        }
        
        // Sumar
        $content .= "E1EDS01 " . str_pad("SUMME", 20) . str_pad(number_format($order->total_amount, 2, '.', ''), 15, ' ', STR_PAD_LEFT) . "\n";
        
        return $content;
    }

    /**
     * Generează conținutul confirmării de comandă folosind șablonul
     *
     * @param Order $order Comanda
     * @return string Conținutul confirmării
     */
    private function generateConfirmationContent(Order $order): string
    {
        $template = $this->getTemplateContent($this->templates['confirmare']);
        
        // Înlocuim placeholder-ele cu date reale
        $content = str_replace('[NUMAR_COMANDA]', $order->order_number, $template);
        $content = str_replace('[DATA]', Carbon::now()->format('Y-m-d H:i:s'), $content);
        $content = str_replace('[NUME_FURNIZOR]', $order->supplier->organization->name, $content);
        $content = str_replace('[ID_FURNIZOR]', $order->supplier->connect_id, $content);
        $content = str_replace('[NUME_CLIENT]', $order->client->organization->name, $content);
        $content = str_replace('[ID_CLIENT]', $order->client->connect_id, $content);
        
        // Generăm liniile pentru produse
        $productLines = '';
        $i = 1;
        
        foreach ($order->items as $item) {
            $prod = $item->product;
            $content = str_replace("[COD_$i]", $prod->code, $content);
            $content = str_replace("[DESC_$i]", $prod->description, $content);
            $content = str_replace("[CANT_$i]", $item->quantity, $content);
            $content = str_replace("[PRET_$i]", number_format($item->unit_price, 2) . " RON", $content);
            $i++;
        }

        // Eliminăm liniile rămase necompletate
        $content = preg_replace('/\[COD_\d+\].*\[PRET_\d+\]/m', '', $content);
        
        // Înlocuim suma totală
        $content = str_replace('[SUMA_TOTALA]', number_format($order->total_amount, 2) . " RON", $content);
        
        return $content;
    }

    /**
     * Generează conținutul avizului de livrare folosind șablonul
     *
     * @param Order $order Comanda
     * @return string Conținutul avizului
     */
    private function generateDeliveryNoteContent(Order $order): string
    {
        $template = $this->getTemplateContent($this->templates['aviz']);
        
        // Generăm un număr de aviz unic
        $avizNumber = 'AVZ-' . date('Ymd') . '-' . $order->order_number;
        
        // Înlocuim placeholder-ele cu date reale
        $content = str_replace('[NUMAR_AVIZ]', $avizNumber, $template);
        $content = str_replace('[DATA]', Carbon::now()->format('Y-m-d H:i:s'), $content);
        $content = str_replace('[NUME_FURNIZOR]', $order->supplier->organization->name, $content);
        $content = str_replace('[ID_FURNIZOR]', $order->supplier->connect_id, $content);
        $content = str_replace('[NUME_CLIENT]', $order->client->organization->name, $content);
        $content = str_replace('[ID_CLIENT]', $order->client->connect_id, $content);
        $content = str_replace('[NUMAR_COMANDA]', $order->order_number, $content);
        
        // Generăm liniile pentru produse
        $i = 1;
        
        foreach ($order->items as $item) {
            $prod = $item->product;
            $content = str_replace("[COD_$i]", $prod->code, $content);
            $content = str_replace("[DESC_$i]", $prod->description, $content);
            $content = str_replace("[CANT_$i]", $item->quantity, $content);
            $content = str_replace("[PRET_$i]", number_format($item->unit_price, 2) . " RON", $content);
            $i++;
        }

        // Eliminăm liniile rămase necompletate
        $content = preg_replace('/\[COD_\d+\].*\[PRET_\d+\]/m', '', $content);
        
        // Înlocuim suma totală
        $content = str_replace('[SUMA_TOTALA]', number_format($order->total_amount, 2) . " RON", $content);
        
        return $content;
    }

    /**
     * Generează conținutul facturii folosind șablonul
     *
     * @param Order $order Comanda
     * @return string Conținutul facturii
     */
    private function generateInvoiceContent(Order $order): string
    {
        $template = $this->getTemplateContent($this->templates['factura']);
        
        // Generăm numere unice pentru factură și aviz
        $facturaNumber = 'FCT-' . date('Ymd') . '-' . $order->order_number;
        $avizNumber = 'AVZ-' . date('Ymd') . '-' . $order->order_number;
        
        // Înlocuim placeholder-ele cu date reale
        $content = str_replace('[NUMAR_FACTURA]', $facturaNumber, $template);
        $content = str_replace('[DATA]', Carbon::now()->format('Y-m-d H:i:s'), $content);
        $content = str_replace('[NUME_FURNIZOR]', $order->supplier->organization->name, $content);
        $content = str_replace('[ID_FURNIZOR]', $order->supplier->connect_id, $content);
        $content = str_replace('[CUI_FURNIZOR]', $order->supplier->organization->cui, $content);
        $content = str_replace('[ADRESA_FURNIZOR]', 
            $order->supplier->organization->strada . ' ' . $order->supplier->organization->numar_strada, 
            $content);
        
        $content = str_replace('[NUME_CLIENT]', $order->client->organization->name, $content);
        $content = str_replace('[ID_CLIENT]', $order->client->connect_id, $content);
        $content = str_replace('[CUI_CLIENT]', $order->client->organization->cui, $content);
        $content = str_replace('[ADRESA_CLIENT]', 
            $order->client->organization->strada . ' ' . $order->client->organization->numar_strada, 
            $content);
        
        $content = str_replace('[NUMAR_COMANDA]', $order->order_number, $content);
        $content = str_replace('[NUMAR_AVIZ]', $avizNumber, $content);
        
        // Generăm liniile pentru produse
        $i = 1;
        
        foreach ($order->items as $item) {
            $prod = $item->product;
            $totalItem = $item->quantity * $item->unit_price;
            
            $content = str_replace("[COD_$i]", $prod->code, $content);
            $content = str_replace("[DESC_$i]", $prod->description, $content);
            $content = str_replace("[CANT_$i]", $item->quantity, $content);
            $content = str_replace("[PRET_$i]", number_format($item->unit_price, 2) . " RON", $content);
            $content = str_replace("[TOTAL_$i]", number_format($totalItem, 2) . " RON", $content);
            $i++;
        }

        // Eliminăm liniile rămase necompletate
        $content = preg_replace('/\[COD_\d+\].*\[TOTAL_\d+\]/m', '', $content);
        
        // Înlocuim suma totală
        $content = str_replace('[SUMA_TOTALA]', number_format($order->total_amount, 2) . " RON", $content);
        
        return $content;
    }

    /**
     * Obține conținutul șablonului
     *
     * @param string $templatePath Calea către șablon
     * @return string Conținutul șablonului
     * @throws \Exception Dacă șablonul nu există
     */
    private function getTemplateContent(string $templatePath): string
    {
        if (!file_exists($templatePath)) {
            throw new \Exception("Șablonul $templatePath nu există");
        }
        
        return file_get_contents($templatePath);
    }

    /**
     * Generează un nume de fișier unic
     *
     * @param string $baseName Numele de bază al fișierului
     * @return string Numele unic
     */
    private function generateUniqueFileName(string $baseName): string
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $uniqueId = Str::random(8);
        
        return $baseName . '_' . $timestamp . '_' . $uniqueId . '.txt';
    }

    /**
     * Salvează conținutul într-un fișier
     *
     * @param string $path Calea fișierului
     * @param string $content Conținutul fișierului
     * @return string Calea relativă către fișier
     */
    private function saveFile(string $path, string $content): string
    {
        file_put_contents($path, $content);
        return $path;
    }

    /**
     * Înregistrează un document în baza de date
     *
     * @param Order $order Comanda asociată
     * @param string $type Tipul documentului
     * @param string $filePath Calea către fișier
     * @return void
     */
    private function registerDocument(Order $order, string $type, string $filePath): void
    {
        // Mapare între tipurile noastre și tipurile din model
        $typeMap = [
            'confirmation' => 'confirmation',
            'delivery_note' => 'delivery_note',
            'invoice' => 'invoice'
        ];
        
        $documentType = $typeMap[$type] ?? $type;
        
        $order->documents()->create([
            'type' => $documentType,
            'file_path' => $filePath,
            'customer_id' => $order->client_id,
            'supplier_id' => $order->supplier_id,
            'status' => 'issued',
            'total_amount' => $order->total_amount,
            'currency' => 'RON',
            'issue_date' => Carbon::now(),
            'number' => $this->generateDocumentNumber($type, $order->order_number)
        ]);
    }

    /**
     * Generează un număr unic pentru document
     *
     * @param string $type Tipul documentului
     * @param string $orderNumber Numărul comenzii
     * @return string Numărul documentului
     */
    private function generateDocumentNumber(string $type, string $orderNumber): string
    {
        $prefix = match($type) {
            'confirmation' => 'CONF',
            'delivery_note' => 'AVZ',
            'invoice' => 'FCT',
            default => 'DOC'
        };
        
        return $prefix . '-' . date('Ymd') . '-' . $orderNumber;
    }

    /**
     * Verifică și creează directoarele necesare dacă acestea nu există
     */
    private function ensureDirectoriesExist(): void
    {
        $directories = array_values($this->directories);
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    $this->logError('Nu s-a putut crea directorul: ' . $directory);
                    throw new \Exception("Nu s-a putut crea directorul: $directory");
                }
                $this->logInfo('S-a creat directorul: ' . $directory);
            }
        }
    }

    /**
     * Creează un mesaj IDOC în baza de date
     *
     * @param Order $order Comanda asociată
     * @param string $type Tipul mesajului
     * @param string $direction Direcția mesajului
     * @param string $filePath Calea către fișier
     * @param string $content Conținutul mesajului
     * @return void
     */
    private function createIdocMessage(Order $order, string $type, string $direction, string $filePath, string $content): void
    {
        $order->idocMessages()->create([
            'message_type' => $type,
            'direction' => $direction,
            'file_path' => $filePath,
            'content' => $content,
            'status' => IdocMessage::STATUS_PROCESSED,
            'processed_at' => now(),
            'client_id' => $order->client_id,
            'supplier_id' => $order->supplier_id
        ]);
    }

    /**
     * Înregistrează un mesaj de informare în log
     *
     * @param string $message Mesajul
     * @param array $context Contextul
     */
    private function logInfo(string $message, array $context = []): void
    {
        if ($this->loggingConfig['enabled']) {
            Log::channel($this->loggingConfig['channel'])->info($message, $context);
        }
    }
    
    /**
     * Înregistrează un mesaj de eroare în log
     *
     * @param string $message Mesajul
     * @param array $context Contextul
     */
    private function logError(string $message, array $context = []): void
    {
        if ($this->loggingConfig['enabled']) {
            Log::channel($this->loggingConfig['channel'])->error($message, $context);
        }
    }
} 