<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factură {{ $invoice->numar_factura }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .party-details {
            margin-bottom: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .totals table {
            width: 100%;
        }
        .totals table td {
            padding: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FACTURĂ</h1>
            <h2>Seria {{ substr($invoice->numar_factura, 0, 4) }} Nr. {{ substr($invoice->numar_factura, 4) }}</h2>
        </div>

        <div class="invoice-details">
            <p><strong>Data emiterii:</strong> {{ $invoice->data_emitere->format('d.m.Y') }}</p>
            <p><strong>Data scadentă:</strong> {{ $invoice->data_scadenta->format('d.m.Y') }}</p>
            <p><strong>Număr comandă:</strong> {{ $invoice->order->numar_comanda }}</p>
        </div>

        <div class="party-details">
            <div style="float: left; width: 45%;">
                <h3>Furnizor</h3>
                <p><strong>{{ $invoice->supplier->name }}</strong></p>
                <p>{{ $invoice->supplier->email }}</p>
                <!-- Adăugați aici alte detalii despre furnizor -->
            </div>

            <div style="float: right; width: 45%;">
                <h3>Client</h3>
                <p><strong>{{ $invoice->customer->name }}</strong></p>
                <p>{{ $invoice->customer->email }}</p>
                <p>{{ $invoice->order->adresa_livrare }}</p>
            </div>
        </div>

        <div class="clearfix"></div>

        <table class="table">
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Cod Produs</th>
                    <th>Descriere</th>
                    <th>Cantitate</th>
                    <th>Preț Unitar</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->order->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->cod_produs }}</td>
                        <td>{{ $item->product->descriere }}</td>
                        <td>{{ $item->cantitate }}</td>
                        <td>{{ number_format($item->pret_unitar, 2) }} RON</td>
                        <td>{{ number_format($item->subtotal, 2) }} RON</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td>{{ number_format($invoice->subtotal, 2) }} RON</td>
                </tr>
                <tr>
                    <td><strong>TVA (19%):</strong></td>
                    <td>{{ number_format($invoice->tva, 2) }} RON</td>
                </tr>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td>{{ number_format($invoice->total, 2) }} RON</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        @if($invoice->mentiuni)
            <div style="margin-top: 30px;">
                <h3>Mențiuni</h3>
                <p>{{ $invoice->mentiuni }}</p>
            </div>
        @endif

        <div class="footer">
            <p>Factură generată automat prin sistemul B2B</p>
            <p>Document valid fără semnătură și ștampilă conform Art. 319 alin. (29) din Legea 227/2015 privind Codul Fiscal</p>
        </div>
    </div>
</body>
</html> 