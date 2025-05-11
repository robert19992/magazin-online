<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\DeliveryNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:supplier']);
    }

    /**
     * Afișează arhiva de documente pentru client
     */
    public function index()
    {
        $user = auth()->user();
        
        $documents = Document::where('customer_id', $user->id)
            ->with(['order.supplier'])
            ->latest()
            ->paginate(10);

        return view('documents.index', compact('documents'));
    }

    /**
     * Afișează un document specific
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document);
        
        return view('documents.show', compact('document'));
    }

    /**
     * Descarcă un document
     */
    public function download(Document $document)
    {
        $this->authorize('download', $document);
        
        if (!Storage::exists($document->file_path)) {
            abort(404, 'Documentul nu a fost găsit.');
        }

        return Storage::download($document->file_path, $document->original_filename);
    }

    /**
     * Grupează documentele după comandă
     */
    public function groupByOrder(Request $request)
    {
        if (!auth()->user()->isCustomer()) {
            abort(403);
        }

        $orders = Order::where('customer_id', auth()->id())
            ->with(['documents', 'supplier'])
            ->whereHas('documents')
            ->latest()
            ->paginate(10);

        return view('documents.group-by-order', compact('orders'));
    }

    /**
     * Caută documente
     */
    public function search(Request $request)
    {
        if (!auth()->user()->isCustomer()) {
            abort(403);
        }

        $query = Document::where('customer_id', auth()->id())
            ->with(['order', 'supplier']);

        // Filtrare după tip document
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filtrare după dată
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Căutare după număr document sau comandă
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('order', function($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $documents = $query->latest()->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($documents);
        }

        return view('documents.index', compact('documents'));
    }

    /**
     * Exportă documentele selectate
     */
    public function export(Request $request)
    {
        if (!auth()->user()->isCustomer()) {
            abort(403);
        }

        $validated = $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'exists:documents,id'
        ]);

        $documents = Document::whereIn('id', $validated['documents'])
            ->where('customer_id', auth()->id())
            ->get();

        // Creăm un ZIP cu documentele selectate
        $zip = new \ZipArchive();
        $zipName = 'documente_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($documents as $document) {
                if (Storage::exists($document->file_path)) {
                    $fileName = $document->type . '_' . $document->number . '.pdf';
                    $zip->addFromString(
                        $fileName,
                        Storage::get($document->file_path)
                    );
                }
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend();
        }

        return back()->with('error', 'Nu s-a putut crea arhiva ZIP.');
    }

    public function sent()
    {
        $orders = Order::where('supplier_id', auth()->id())
            ->with(['customer', 'items.product'])
            ->latest()
            ->paginate(10);

        $invoices = Invoice::where('supplier_id', auth()->id())
            ->with(['order.customer'])
            ->latest()
            ->paginate(10);

        $deliveryNotes = DeliveryNote::where('supplier_id', auth()->id())
            ->with(['order.customer'])
            ->latest()
            ->paginate(10);

        return view('documents.sent', compact('orders', 'invoices', 'deliveryNotes'));
    }

    public function generateInvoice(Order $order)
    {
        $this->authorize('view', $order);

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'supplier_id' => auth()->id(),
            'customer_id' => $order->customer_id,
            'numar_factura' => 'F' . date('Ymd') . str_pad($order->id, 4, '0', STR_PAD_LEFT),
            'data_emitere' => now(),
            'data_scadenta' => now()->addDays(30),
            'total' => $order->total_value,
            'status' => 'emisa'
        ]);

        // Generăm PDF-ul facturii
        $pdf = PDF::loadView('documents.invoice-pdf', [
            'invoice' => $invoice,
            'order' => $order,
            'items' => $order->items,
            'customer' => $order->customer,
            'supplier' => auth()->user()
        ]);

        // Salvăm PDF-ul
        $pdfPath = 'invoices/' . $invoice->numar_factura . '.pdf';
        Storage::put($pdfPath, $pdf->output());

        $invoice->update(['pdf_path' => $pdfPath]);

        return redirect()->route('documents.sent')
            ->with('success', 'Factura a fost generată cu succes.');
    }

    public function generateDeliveryNote(Order $order)
    {
        $this->authorize('view', $order);

        $deliveryNote = DeliveryNote::create([
            'order_id' => $order->id,
            'supplier_id' => auth()->id(),
            'customer_id' => $order->customer_id,
            'numar_aviz' => 'AV' . date('Ymd') . str_pad($order->id, 4, '0', STR_PAD_LEFT),
            'data_emitere' => now(),
            'status' => 'emisa'
        ]);

        // Generăm PDF-ul avizului
        $pdf = PDF::loadView('documents.delivery-note-pdf', [
            'deliveryNote' => $deliveryNote,
            'order' => $order,
            'items' => $order->items,
            'customer' => $order->customer,
            'supplier' => auth()->user()
        ]);

        // Salvăm PDF-ul
        $pdfPath = 'delivery-notes/' . $deliveryNote->numar_aviz . '.pdf';
        Storage::put($pdfPath, $pdf->output());

        $deliveryNote->update(['pdf_path' => $pdfPath]);

        return redirect()->route('documents.sent')
            ->with('success', 'Avizul de expediție a fost generat cu succes.');
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        if (!Storage::exists($invoice->pdf_path)) {
            return back()->with('error', 'Fișierul facturii nu a fost găsit.');
        }

        return Storage::download($invoice->pdf_path, 'factura_' . $invoice->numar_factura . '.pdf');
    }

    public function downloadDeliveryNote(DeliveryNote $deliveryNote)
    {
        $this->authorize('view', $deliveryNote);

        if (!Storage::exists($deliveryNote->pdf_path)) {
            return back()->with('error', 'Fișierul avizului nu a fost găsit.');
        }

        return Storage::download($deliveryNote->pdf_path, 'aviz_' . $deliveryNote->numar_aviz . '.pdf');
    }

    public function markInvoiceAsPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->update([
            'status' => 'platita',
            'data_plata' => now()
        ]);

        return back()->with('success', 'Factura a fost marcată ca plătită.');
    }
} 