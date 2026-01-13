<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Számla - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #f5f7fa;
        }
        .invoice-container {
            background: white;
            padding: 30px;
            max-width: 210mm;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        .supplier-info {
            flex: 1;
        }
        .supplier-info h2 {
            color: #667eea;
            font-size: 18pt;
            margin: 0 0 10px 0;
        }
        .supplier-info p {
            margin: 3px 0;
            font-size: 9pt;
            color: #333;
        }
        .invoice-title {
            text-align: center;
            flex: 1;
        }
        .invoice-title h1 {
            font-size: 24pt;
            color: #667eea;
            margin: 0;
        }
        .invoice-number {
            text-align: right;
            flex: 1;
        }
        .invoice-number p {
            margin: 3px 0;
            font-size: 10pt;
        }
        .parties {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .supplier, .customer {
            flex: 1;
            margin: 0 15px;
        }
        .supplier h3, .customer h3 {
            font-size: 12pt;
            margin: 0 0 10px 0;
            color: #1f2937;
        }
        .supplier p, .customer p {
            margin: 4px 0;
            font-size: 9pt;
            color: #4b5563;
        }
        .payment-info {
            margin: 20px 0;
            padding: 15px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        .payment-info p {
            margin: 3px 0;
            font-size: 9pt;
            color: #92400e;
        }
        .payment-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .payment-table td {
            padding: 8px;
            font-size: 9pt;
            border-bottom: 1px solid #e5e7eb;
        }
        .payment-table td:first-child {
            font-weight: 600;
            color: #6b7280;
        }
        .items-table {
            width: 100%;
            margin: 30px 0;
            border-collapse: collapse;
        }
        .items-table th {
            background: #667eea;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 10pt;
            font-weight: 600;
        }
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        .items-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .tax-summary {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .tax-summary th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-size: 10pt;
            font-weight: 600;
            border: 1px solid #e5e7eb;
        }
        .tax-summary td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            font-size: 10pt;
        }
        .tax-summary .total-row {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        .total-amount {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #eff6ff;
            border-radius: 8px;
        }
        .total-amount h2 {
            font-size: 20pt;
            color: #667eea;
            margin: 0 0 10px 0;
        }
        .total-amount .amount {
            font-size: 24pt;
            font-weight: 700;
            color: #1f2937;
        }
        .total-amount .amount-text {
            font-size: 11pt;
            color: #6b7280;
            margin-top: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="supplier-info">
                <h2>{{ $hotel->name }}</h2>
                <p>{{ $hotel->location }}</p>
                <p>Adószám: {{ $hotel->user->tax_number ?? 'N/A' }}</p>
                <p>Bankszámlaszám: {{ $hotel->user->bank_account ?? 'N/A' }}</p>
                @if($hotel->user->eu_tax_number)
                <p>Közösségi adószám: {{ $hotel->user->eu_tax_number }}</p>
                @endif
            </div>
            <div class="invoice-title">
                <h1>SZÁMLA</h1>
            </div>
            <div class="invoice-number">
                <p><strong>Bizonylat sorszáma:</strong></p>
                <p style="font-size: 12pt; font-weight: 600;">{{ $invoice->invoice_number }}</p>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="supplier">
                <h3>Szállító:</h3>
                <p><strong>{{ $hotel->name }}</strong></p>
                <p>{{ $hotel->location }}</p>
                <p>Bankszámlaszám: {{ $hotel->user->bank_account ?? 'N/A' }}</p>
                <p>Adószám: {{ $hotel->user->tax_number ?? 'N/A' }}</p>
                @if($hotel->user->eu_tax_number)
                <p>Közösségi adószám: {{ $hotel->user->eu_tax_number }}</p>
                @endif
            </div>
            <div class="customer">
                <h3>Vevő:</h3>
                <p><strong>{{ $guest->name }}</strong></p>
                <p>E-mail: {{ $guest->email }}</p>
                <p>Adószám: {{ $guest->tax_number ?? 'N/A' }}</p>
                @if($guest->eu_tax_number)
                <p>Közösségi adószám: {{ $guest->eu_tax_number }}</p>
                @endif
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <p><strong>Figyelem!</strong> Az ÁFA törvény XIII / A fejezete alapján pénzforgalmi elszámolás alá tartozó bizonylat.</p>
        </div>

        <!-- Payment Terms Table -->
        <table class="payment-table">
            <tr>
                <td>Fizetési mód</td>
                <td>Átutalás (8 nap)</td>
                <td>Elszámolási időszak</td>
                <td></td>
            </tr>
            <tr>
                <td>Teljesítés</td>
                <td>{{ \Carbon\Carbon::parse($booking->startDate)->format('Y.m.d') }}</td>
                <td>Kelte</td>
                <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('Y.m.d') }}</td>
            </tr>
            <tr>
                <td>Fizetési határidő</td>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y.m.d') }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Megnevezés</th>
                    <th class="text-center">Vtsz/Teszor</th>
                    <th class="text-right">Egységár</th>
                    <th class="text-center">Mennyiség</th>
                    <th class="text-center">Áfa(%)</th>
                    <th class="text-right">Nettó</th>
                    <th class="text-right">Áfa</th>
                    <th class="text-right">Bruttó</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $itemsNetTotal = 0;
                    $itemsTaxTotal = 0;
                    $itemsGrossTotal = 0;
                @endphp
                @foreach($items as $item)
                @php
                    $netPrice = $item['total'];
                    $taxAmount = round($netPrice * ($invoice->tax_rate / 100), 2);
                    $grossPrice = $netPrice + $taxAmount;
                    $itemsNetTotal += $netPrice;
                    $itemsTaxTotal += $taxAmount;
                    $itemsGrossTotal += $grossPrice;
                @endphp
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-center">0</td>
                    <td class="text-right">{{ number_format($item['unit_price'], 2, ',', ' ') }} Ft</td>
                    <td class="text-center">{{ $item['quantity'] }} @if($item['type'] === 'room') éjszaka @else alkalom @endif</td>
                    <td class="text-center">{{ $invoice->tax_rate }}%</td>
                    <td class="text-right">{{ number_format($netPrice, 2, ',', ' ') }} Ft</td>
                    <td class="text-right">{{ number_format($taxAmount, 2, ',', ' ') }} Ft</td>
                    <td class="text-right">{{ number_format($grossPrice, 2, ',', ' ') }} Ft</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tax Summary -->
        <table class="tax-summary">
            <thead>
                <tr>
                    <th>Áfa összesítő</th>
                    <th class="text-right">Nettó</th>
                    <th class="text-right">Áfa</th>
                    <th class="text-right">Bruttó</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->tax_rate }}%-os áfa</td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }} Ft</td>
                    <td class="text-right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }} Ft</td>
                    <td class="text-right">{{ number_format($invoice->total_amount, 2, ',', ' ') }} Ft</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Összesen:</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->subtotal, 2, ',', ' ') }} Ft</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->tax_amount, 2, ',', ' ') }} Ft</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->total_amount, 2, ',', ' ') }} Ft</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Total Amount -->
        <div class="total-amount">
            <h2>Fizetendő végösszeg:</h2>
            <div class="amount">{{ number_format($invoice->total_amount, 0, ',', ' ') }} Ft</div>
            <div class="amount-text">Azaz, {{ \App\Helpers\NumberToWords::convert((int)$invoice->total_amount) }} Forint</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>HotelFlow rendszer - Automatikusan generált számla</p>
            <p>Generálva: {{ now()->format('Y.m.d H:i') }}</p>
        </div>
    </div>
</body>
</html>
