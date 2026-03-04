<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Számla</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .email-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        h1 {
            color: #667eea;
            font-size: 1.75rem;
            margin: 0 0 10px 0;
        }
        .content {
            margin-bottom: 30px;
        }
        p {
            margin-bottom: 15px;
            color: #4b5563;
        }
        .invoice-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .invoice-details h3 {
            color: #1f2937;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #1f2937;
        }
        .info-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 0;
            color: #065f46;
            font-size: 0.9rem;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🏨</div>
            <h1>HotelFlow</h1>
        </div>

        <div class="content">
            <p><strong>Kedves {{ $invoice->booking->user->name }}!</strong></p>

            <p>Köszönjük a foglalásodat!</p>

            <p>A foglalásodhoz tartozó számlát csatoljuk ezen e-mail mellé PDF formátumban.</p>

            <div class="invoice-details">
                <h3>Számla részletei</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Számlaszám:</span>
                    <span class="detail-value">{{ $invoice->invoice_number }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Foglalás azonosító:</span>
                    <span class="detail-value">#{{ $invoice->booking->id }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Szálloda:</span>
                    <span class="detail-value">{{ $invoice->booking->hotel->name }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Kibocsátás dátuma:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('Y.m.d') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Fizetési határidő:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y.m.d') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Fizetendő összeg:</span>
                    <span class="detail-value"><strong>€{{ number_format($invoice->total_amount, 2, ',', ' ') }}</strong></span>
                </div>
            </div>

            <div class="info-box" style="background: #dbeafe; border-left-color: #3b82f6;">
                <p><strong>Fizetési mód: Bankkártya</strong></p>
                <p style="margin: 10px 0;">
                    <a href="{{ $paymentUrl }}" style="display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                        Fizetés bankkártyával
                    </a>
                </p>
                <p style="margin-top: 10px; font-size: 0.85rem;">Kattints a gombra a biztonságos fizetési oldal megnyitásához. A fizetés sikeres befejezése után automatikusan elküldjük a check-in QR kódot.</p>
            </div>

            <div class="info-box">
                <p><strong>Fontos információ:</strong></p>
                <p>A számla PDF formátumban csatolva van ehhez az e-mailhez. A számlát a foglalási menüben is letöltheted.</p>
            </div>

            <p>Ha bármilyen kérdésed van a számlával kapcsolatban, kérjük, vedd fel a kapcsolatot a szállodával.</p>
        </div>

        <div class="footer">
            <p>Üdvözlettel,<br><strong>HotelFlow csapat</strong></p>
            <p style="margin-top: 15px; font-size: 0.8rem;">
                Ez egy automatikus üzenet, kérjük ne válaszolj rá.
            </p>
        </div>
    </div>
</body>
</html>
