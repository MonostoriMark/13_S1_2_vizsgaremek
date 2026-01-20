<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Számla - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: white;
        }
        .invoice-container {
            background: white;
            padding: 20px;
            max-width: 210mm;
            margin: 0 auto;
        }
        
        /* Header Section with Logo */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e8f5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .logo-bars {
            font-size: 20px;
            color: #4caf50;
            line-height: 1;
        }
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        .logo-main {
            font-size: 24pt;
            font-weight: bold;
            color: #4caf50;
            line-height: 1.1;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        .logo-sub {
            font-size: 9pt;
            color: #666;
            margin-top: 2px;
        }
        .invoice-title-section {
            flex: 1;
            text-align: center;
        }
        .invoice-title {
            font-size: 28pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            letter-spacing: 2px;
            margin: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        .invoice-number-section {
            text-align: right;
        }
        .invoice-number-label {
            font-size: 10pt;
            color: #666;
            margin-bottom: 5px;
        }
        .invoice-number-value {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
        }
        
        /* Issuer and Customer Section */
        .parties-section {
            margin: 20px 0;
            border: 1px solid #d0d0d0;
        }
        .parties-header {
            display: flex;
            background: #b3d9ff;
        }
        .party-header {
            flex: 1;
            padding: 8px 12px;
            font-weight: 600;
            font-size: 10pt;
            color: #000;
            border-right: 1px solid #d0d0d0;
        }
        .party-header:last-child {
            border-right: none;
        }
        .parties-content {
            display: flex;
        }
        .party-content {
            flex: 1;
            padding: 12px;
            border-right: 1px solid #d0d0d0;
            font-size: 10pt;
            line-height: 1.6;
        }
        .party-content:last-child {
            border-right: none;
        }
        .party-content p {
            margin: 3px 0;
        }
        
        /* Payment and Date Information */
        .payment-dates-section {
            margin: 20px 0;
            border: 1px solid #d0d0d0;
        }
        .payment-dates-header {
            display: flex;
            background: #b3d9ff;
        }
        .payment-date-header {
            flex: 1;
            padding: 8px 12px;
            font-weight: 600;
            font-size: 10pt;
            color: #000;
            border-right: 1px solid #d0d0d0;
            text-align: center;
        }
        .payment-date-header:last-child {
            border-right: none;
        }
        .payment-dates-content {
            display: flex;
        }
        .payment-date-content {
            flex: 1;
            padding: 10px 12px;
            border-right: 1px solid #d0d0d0;
            font-size: 10pt;
            text-align: center;
        }
        .payment-date-content:last-child {
            border-right: none;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            border: 1px solid #d0d0d0;
        }
        .items-table th {
            background: #f5f5f5;
            padding: 10px 8px;
            text-align: left;
            font-size: 10pt;
            font-weight: 600;
            border: 1px solid #d0d0d0;
            border-bottom: 2px solid #d0d0d0;
        }
        .items-table th.text-right {
            text-align: right;
        }
        .items-table td {
            padding: 10px 8px;
            border: 1px solid #d0d0d0;
            font-size: 10pt;
        }
        .items-table td.text-right {
            text-align: right;
        }
        .items-table td.text-center {
            text-align: center;
        }
        
        /* Sub-total and Total Rows */
        .subtotal-row {
            background: #f9f9f9;
        }
        .subtotal-row td {
            font-size: 9pt;
            padding: 8px;
        }
        .total-row {
            background: #f0f0f0;
            font-weight: bold;
        }
        .total-row td {
            padding: 12px 8px;
            font-size: 11pt;
        }
        
        /* Final Total Section */
        .final-total {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
        }
        .final-total-main {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .final-total-secondary {
            font-size: 10pt;
            color: #666;
            margin-top: 5px;
        }
        .exchange-rate {
            font-size: 9pt;
            color: #999;
            margin-top: 5px;
        }
        
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header with Logo -->
        <div class="header-section">
            <div class="logo-section">
                <div class="logo-circle">
                    <div class="logo-bars">▬▬▬</div>
                </div>
                <div class="logo-text">
                    <div class="logo-main">PÉLDA</div>
                    <div class="logo-sub">LOGO</div>
                </div>
            </div>
            <div class="invoice-title-section">
                <h1 class="invoice-title">SZÁMLA / INVOICE</h1>
            </div>
            <div class="invoice-number-section">
                <div class="invoice-number-label">Számlaszám / Invoice number:</div>
                <div class="invoice-number-value">{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <!-- Issuer and Customer Details -->
        <div class="parties-section">
            <div class="parties-header">
                <div class="party-header">Számla kiállító adatai/Invoice issuer:</div>
                <div class="party-header">Vevő adatai/Customer:</div>
            </div>
            <div class="parties-content">
                <div class="party-content">
                    <p><strong>{{ $hotel->name }}</strong></p>
                    <p>{{ $hotel->location ?? 'N/A' }}</p>
                    <p>Adószám / Tax number: {{ $hotel->user->tax_number ?? 'N/A' }}</p>
                    @if($hotel->user->eu_tax_number)
                    <p>EU adószám / EU tax number: {{ $hotel->user->eu_tax_number }}</p>
                    @endif
                    <p>Bankszámlaszám / Bank account:</p>
                    @if($hotel->user->bank_name)
                    <p>{{ $hotel->user->bank_name }}</p>
                    @endif
                    @if($hotel->user->bank_account)
                    <p>{{ $hotel->user->bank_account }}</p>
                    @endif
                    @if($hotel->user->swift_code)
                    <p>SWIFT: {{ $hotel->user->swift_code }}</p>
                    @endif
                </div>
                <div class="party-content">
                    @php
                        $custName = $invoiceDetails->full_name ?? ($booking->user->name ?? 'N/A');
                        $custEmail = $invoiceDetails->email ?? ($booking->user->email ?? null);
                        $custType = $invoiceDetails->customer_type ?? 'private';
                    @endphp
                    <p><strong>{{ $custName }}</strong></p>
                    @if(($custType === 'business') && ($invoiceDetails->company_name ?? null))
                    <p>{{ $invoiceDetails->company_name }}</p>
                    @endif
                    @if($invoiceDetails && ($invoiceDetails->address_line || $invoiceDetails->postal_code || $invoiceDetails->city || $invoiceDetails->country))
                    <p>
                        {{ $invoiceDetails->address_line ?? '' }}
                        @if($invoiceDetails->postal_code || $invoiceDetails->city)
                            <br>{{ trim(($invoiceDetails->postal_code ?? '') . ' ' . ($invoiceDetails->city ?? '')) }}
                        @endif
                        @if($invoiceDetails->country)
                            <br>{{ $invoiceDetails->country }}
                        @endif
                    </p>
                    @elseif($booking->user->address)
                    <p>{{ $booking->user->address }}</p>
                    @endif
                    @if($custEmail)
                    <p>{{ $custEmail }}</p>
                    @endif
                    @if($invoiceDetails && $invoiceDetails->tax_number)
                    <p>Adószám / Tax number: {{ $invoiceDetails->tax_number }}</p>
                    @elseif($booking->user->tax_number)
                    <p>Adószám / Tax number: {{ $booking->user->tax_number }}</p>
                    @endif
                    @if($booking->user->eu_tax_number)
                    <p>EU adószám / EU tax number: {{ $booking->user->eu_tax_number }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment and Date Information -->
        <div class="payment-dates-section">
            <div class="payment-dates-header">
                <div class="payment-date-header">Fizetési mód</div>
                <div class="payment-date-header">Számla kelte</div>
                <div class="payment-date-header">Teljesítés időpontja</div>
                <div class="payment-date-header">Fizetési határidő</div>
            </div>
            <div class="payment-dates-content">
                <div class="payment-date-content">
                    @php
                        $method = $payment->method ?? 'bank_transfer';
                    @endphp
                    @if($method === 'bank_transfer')
                        átutalás / transfer
                    @else
                        {{ $method }}
                    @endif
                </div>
                <div class="payment-date-content">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('Y.m.d.') }}</div>
                <div class="payment-date-content">{{ \Carbon\Carbon::parse($booking->endDate)->format('Y.m.d.') }}</div>
                <div class="payment-date-content">{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y.m.d.') }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Megnevezés</th>
                    <th class="text-right">Menny.Mee.</th>
                    <th class="text-right">Nettó egységár</th>
                    <th class="text-right">ÁFA</th>
                    <th class="text-right">Nettó érték</th>
                    <th class="text-right">ÁFA érték</th>
                    <th class="text-right">Bruttó érték</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $itemIndex = 1;
                    $totalNet = 0;
                    $totalTax = 0;
                    $totalGross = 0;
                @endphp
                @foreach($items as $item)
                @php
                    $netPrice = $item['total'];
                    $taxAmount = round($netPrice * ($invoice->tax_rate / 100), 2);
                    $grossPrice = $netPrice + $taxAmount;
                    $totalNet += $netPrice;
                    $totalTax += $taxAmount;
                    $totalGross += $grossPrice;
                @endphp
                <tr>
                    <td>{{ $itemIndex++ }}.</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-right">{{ number_format($item['quantity'], 2, ',', '') }}db/pc</td>
                    <td class="text-right">{{ number_format($item['unit_price'], 2, ',', ' ') }} EUA</td>
                    <td class="text-right"></td>
                    <td class="text-right">{{ number_format($netPrice, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($taxAmount, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($grossPrice, 2, ',', ' ') }}</td>
                </tr>
                @endforeach
                
                <!-- Sub-total Row -->
                <tr class="subtotal-row">
                    <td colspan="4">(EU-s termékértékesítés/Tax-free) EUA</td>
                    <td class="text-right"></td>
                    <td class="text-right">{{ number_format($totalNet, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($totalTax, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($totalGross, 2, ',', ' ') }}</td>
                </tr>
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="4"><strong>Összesen / Total:</strong></td>
                    <td class="text-right"></td>
                    <td class="text-right"><strong>{{ number_format($totalNet, 2, ',', ' ') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalTax, 2, ',', ' ') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalGross, 2, ',', ' ') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Final Total Section -->
        <div class="final-total">
            <div class="final-total-main">
                Fizetendő végösszeg / Total invoice value: {{ number_format($invoice->total_amount, 2, ',', ' ') }} EUR
            </div>
            @php
                // Convert EUR to HUF (example rate, should be configurable)
                $eurToHufRate = 383.6300;
                $totalHuf = round($invoice->total_amount * $eurToHufRate, 0);
                $taxHuf = round($invoice->tax_amount * $eurToHufRate, 0);
            @endphp
            <div class="final-total-secondary">
                ÁFA tartalom / VAT amount {{ number_format($taxHuf, 0, ',', ' ') }} Ft, 
                Összesen / Total {{ number_format($totalHuf, 0, ',', ' ') }} Ft.
            </div>
            <div class="exchange-rate">
                (1 EUR={{ number_format($eurToHufRate, 4, ',', '') }} Ft)
            </div>
        </div>
    </div>
</body>
</html>
