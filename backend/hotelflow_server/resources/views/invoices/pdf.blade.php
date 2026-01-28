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
            font-size: 10.5pt;
            line-height: 1.45;
            color: #000;
            background: white;
        }
        .invoice-container {
            background: white;
            padding: 0;
            max-width: 210mm;
            margin: 0 auto;
        }

        /* NAV-style blocks */
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .brand {
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .invoice-type {
            font-size: 18pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            margin: 12px 0 8px 0;
        }
        .inv-no {
            text-align: right;
            font-size: 10.5pt;
        }
        .inv-no strong {
            font-size: 11.5pt;
        }

        .two-col {
            display: flex;
            gap: 20px;
            margin: 12px 0 10px 0;
        }
        .col {
            flex: 1;
        }
        .label {
            color: #222;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .block {
            border: 1px solid #000;
            border-radius: 0;
            padding: 10px 12px;
            min-height: 78px;
        }
        .line {
            margin: 2px 0;
        }
        .muted {
            color: #444;
        }

        .meta {
            margin: 10px 0 8px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
        }
        .meta .kv {
            min-width: 190px;
        }
        .meta .k {
            font-weight: 700;
        }

        .currency {
            margin: 6px 0 10px 0;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 7px;
            font-size: 9.5pt;
            vertical-align: top;
        }
        th {
            background: #fff;
            font-weight: 700;
        }
        .right { text-align: right; }
        .center { text-align: center; }

        .note-row {
            font-size: 9pt;
        }
        .summary {
            margin-top: 10px;
            border: 1px solid #000;
            padding: 8px 10px;
        }
        .summary-title {
            font-weight: 800;
            margin-bottom: 6px;
        }
        .summary-grid {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .summary-grid .left-col {
            flex: 1 1 55%;
            min-width: 260px;
        }
        .summary-grid .right-col {
            flex: 1 1 40%;
            min-width: 220px;
        }
        .sum-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 3px 0;
        }
        .sum-line .k { font-weight: 700; }
        .payable {
            margin-top: 8px;
            font-size: 12pt;
            font-weight: 900;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        .footer {
            margin-top: 12px;
            font-size: 8.8pt;
            color: #111;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .page {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        @php
            $issueDate = \Carbon\Carbon::parse($invoice->issue_date)->format('Y.m.d.');
            $performanceDate = \Carbon\Carbon::parse($booking->endDate)->format('Y.m.d.');
            $dueDate = \Carbon\Carbon::parse($invoice->due_date)->format('Y.m.d.');

            $method = $payment->method ?? 'bank_transfer';
            $paymentMethodHu = $method === 'bank_transfer' ? 'Átutalás' : ($method === 'cash' ? 'Készpénz' : $method);

            $currency = config('app.invoice_currency', 'HUF');
            $eurToHufRate = (float) config('app.invoice_eur_to_huf_rate', 383.63);

            // Heuristic: internal amounts look like EUR if they are small decimals and frontend shows €,
            // but invoices in HU usually must be printed in HUF. We convert only when configured currency is HUF.
            $useConversion = strtoupper($currency) === 'HUF';

            $fmtMoney = function ($amount, $decimals = 2) {
                return number_format((float) $amount, $decimals, ',', ' ');
            };
            $fmtIntMoney = function ($amount) {
                return number_format((float) $amount, 0, ',', ' ');
            };
            $toPrint = function ($amount) use ($useConversion, $eurToHufRate) {
                return $useConversion ? round(((float) $amount) * $eurToHufRate, 0) : (float) $amount;
            };

            $taxRate = (int) ($invoice->tax_rate ?? 0);
            $vatLabel = $taxRate === 0 ? 'AAM' : ($taxRate . '%');

            $custName = $invoiceDetails->full_name ?? ($booking->user->name ?? 'N/A');
            $custEmail = $invoiceDetails->email ?? ($booking->user->email ?? null);
            $custType = $invoiceDetails->customer_type ?? 'private';
            $custCompany = ($custType === 'business') ? ($invoiceDetails->company_name ?? null) : null;
        @endphp

        <div class="top-row">
            <div class="brand">{{ $hotel->name }}</div>
            <div class="inv-no">
                <div><span class="muted">Sorszám:</span> <strong>{{ $invoice->invoice_number }}</strong></div>
            </div>
        </div>

        <div class="invoice-type">SZÁMLA</div>

        <div class="two-col">
            <div class="col">
                <div class="label">Eladó:</div>
                <div class="block">
                    <div class="line"><strong>{{ $hotel->user->name ?? $hotel->name }}</strong></div>
                    <div class="line">{{ $hotel->location ?? '' }}</div>
                    @if($hotel->user->tax_number)
                        <div class="line">Adószám: {{ $hotel->user->tax_number }}</div>
                    @endif
                    @if($hotel->user->bank_account)
                        <div class="line">Bankszámlaszám: {{ $hotel->user->bank_account }}</div>
                    @endif
                </div>
            </div>
            <div class="col">
                <div class="label">Vevő:</div>
                <div class="block">
                    <div class="line"><strong>{{ $custName }}</strong></div>
                    @if($custCompany)
                        <div class="line">{{ $custCompany }}</div>
                    @endif
                    @if($invoiceDetails && ($invoiceDetails->address_line || $invoiceDetails->postal_code || $invoiceDetails->city || $invoiceDetails->country))
                        <div class="line">
                            {{ $invoiceDetails->country ? ($invoiceDetails->country . ' ') : '' }}
                            {{ trim(($invoiceDetails->postal_code ?? '') . ' ' . ($invoiceDetails->city ?? '')) }}
                        </div>
                        @if($invoiceDetails->address_line)
                            <div class="line">{{ $invoiceDetails->address_line }}</div>
                        @endif
                    @elseif($booking->user->address)
                        <div class="line">{{ $booking->user->address }}</div>
                    @endif
                    @if($invoiceDetails && $invoiceDetails->tax_number)
                        <div class="line">Magyar adószám: {{ $invoiceDetails->tax_number }}</div>
                    @elseif($booking->user->tax_number)
                        <div class="line">Magyar adószám: {{ $booking->user->tax_number }}</div>
                    @endif
                    @if($custEmail)
                        <div class="line">{{ $custEmail }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="meta">
            <div class="kv"><span class="k">Fizetési mód:</span> {{ $paymentMethodHu }}</div>
            <div class="kv"><span class="k">Teljesítés:</span> {{ $performanceDate }}</div>
            <div class="kv"><span class="k">Keltezés:</span> {{ $issueDate }}</div>
            <div class="kv"><span class="k">Fizetési határidő:</span> {{ $dueDate }}</div>
        </div>

        <div class="currency">Pénznem: {{ strtoupper($currency) }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 34%;">Tétel neve</th>
                    <th class="center" style="width: 9%;">Mennyiség</th>
                    <th class="center" style="width: 12%;">Mennyiségi egység</th>
                    <th class="center" style="width: 9%;">Áfakulcs</th>
                    <th class="right" style="width: 12%;">Áfa összege</th>
                    <th class="right" style="width: 12%;">Tételsor nettó érték</th>
                    <th class="right" style="width: 12%;">Ellenérték (bruttó)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumNet = 0;
                    $sumVat = 0;
                    $sumGross = 0;
                @endphp
                @foreach($items as $item)
                    @php
                        $net = (float) ($item['total'] ?? 0);
                        $vat = round($net * ($taxRate / 100), 2);
                        $gross = $net + $vat;

                        $pNet = $toPrint($net);
                        $pVat = $toPrint($vat);
                        $pGross = $toPrint($gross);

                        $sumNet += $pNet;
                        $sumVat += $pVat;
                        $sumGross += $pGross;
                    @endphp
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td class="center">{{ $fmtMoney($item['quantity'] ?? 1, 2) }}</td>
                        <td class="center">db</td>
                        <td class="center">{{ $vatLabel }}</td>
                        <td class="right">{{ $fmtIntMoney($pVat) }}</td>
                        <td class="right">{{ $fmtIntMoney($pNet) }}</td>
                        <td class="right">{{ $fmtIntMoney($pGross) }}</td>
                    </tr>
                @endforeach

                @if($taxRate === 0)
                    <tr class="note-row">
                        <td colspan="7">
                            <strong>Alanyi adómentes</strong>
                            <span class="muted"> — Adómentesség leírása: Alanyi adómentes</span>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-title">Számla összesítő:</div>
            <div class="summary-grid">
                <div class="left-col">
                    <div class="sum-line"><span class="k">Számla nettó értéke</span><span>{{ $fmtIntMoney($sumNet) }} {{ strtoupper($currency) }}</span></div>
                    <div class="sum-line"><span class="k">Áthárított áfa összege</span><span>{{ $fmtIntMoney($sumVat) }} {{ strtoupper($currency) }}</span></div>
                    <div class="sum-line"><span class="k">Számla bruttó végösszege</span><span>{{ $fmtIntMoney($sumGross) }} {{ strtoupper($currency) }}</span></div>
                </div>
                <div class="right-col">
                    <div class="sum-line"><span class="k">Áfa százaléka és értéke</span><span></span></div>
                    <div class="sum-line"><span class="k">{{ $vatLabel }}</span><span>{{ $fmtIntMoney($sumVat) }} {{ strtoupper($currency) }}</span></div>
                    @if($taxRate === 0)
                        <div class="sum-line"><span class="k">Alanyi adómentes</span><span></span></div>
                    @endif
                </div>
            </div>
            <div class="payable">
                <div>Fizetendő összeg:</div>
                <div>{{ $fmtIntMoney($sumGross) }} {{ strtoupper($currency) }}</div>
            </div>
            @if($useConversion)
                <div class="muted" style="margin-top: 6px; font-size: 8.8pt;">
                    Tájékoztató: az összegek HUF-ra konvertálva (1 EUR = {{ number_format($eurToHufRate, 4, ',', ' ') }} HUF).
                </div>
            @endif
        </div>

        <div class="footer">
            <div class="muted">Készült: NAV Online Számlázó program formátumát követő sablon</div>
            <div class="page">Oldalszám: <span class="pageNumber"></span>/<span class="pageCount"></span></div>
        </div>

        <script type="text/php">
            if (isset($pdf)) {
                $pdf->page_script('
                    $font = $fontMetrics->get_font("DejaVu Sans", "normal");
                    $pdf->text(520, 820, "Oldalszám: " . $PAGE_NUM . "/" . $PAGE_COUNT, $font, 8);
                ');
            }
        </script>
    </div>
</body>
</html>
