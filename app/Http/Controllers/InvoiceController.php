<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::forSupplier(auth()->id())
            ->with(['order', 'customer'])
            ->latest()
            ->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['order.items.product', 'customer', 'supplier']);

        return view('invoices.show', compact('invoice'));
    }

    public function create(Order $order)
    {
        $this->authorize('create', Invoice::class);

        if ($order->status !== 'livrata') {
            return back()->with('error', 'Factura poate fi emisă doar pentru comenzi livrate.');
        }

        if (Invoice::where('order_id', $order->id)->exists()) {
            return back()->with('error', 'Există deja o factură emisă pentru această comandă.');
        }

        return view('invoices.create', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'data_scadenta' => 'required|date|after:today',
            'mentiuni' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($request, $order) {
            $invoice = new Invoice([
                'numar_factura' => $this->generateInvoiceNumber(),
                'order_id' => $order->id,
                'supplier_id' => $order->supplier_id,
                'customer_id' => $order->customer_id,
                'data_emitere' => now(),
                'data_scadenta' => $request->data_scadenta,
                'mentiuni' => $request->mentiuni,
                'status' => 'emisa'
            ]);

            $invoice->calculateTotals();
            $invoice->save();
        });

        return redirect()->route('invoices.index')
            ->with('success', 'Factura a fost emisă cu succes.');
    }

    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['order.items.product', 'customer', 'supplier']);

        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("factura_{$invoice->numar_factura}.pdf");
    }

    public function markAsPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->markAsPaid();

        return back()->with('success', 'Factura a fost marcată ca plătită.');
    }

    public function markAsCancelled(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->markAsCancelled();

        return back()->with('success', 'Factura a fost anulată.');
    }

    protected function generateInvoiceNumber(): string
    {
        $lastInvoice = Invoice::where('supplier_id', auth()->id())
            ->orderBy('id', 'desc')
            ->first();

        $currentNumber = $lastInvoice ? intval(substr($lastInvoice->numar_factura, -6)) : 0;
        $nextNumber = str_pad($currentNumber + 1, 6, '0', STR_PAD_LEFT);

        return date('Y') . $nextNumber;
    }
} 