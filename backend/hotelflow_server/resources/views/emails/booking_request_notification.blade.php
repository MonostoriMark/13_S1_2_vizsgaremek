<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalási kérés elküldve</title>
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
        .booking-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .booking-details h3 {
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
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 0;
            color: #1e40af;
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
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            background: #fef3c7;
            color: #92400e;
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
            <p><strong>Kedves {{ $booking->user->name }}!</strong></p>

            <p>Köszönjük a foglalási kérésedet!</p>

            <p>A foglalási kérésedet elküldtük a <strong>{{ $booking->hotel->name }}</strong> szállodának. A szálloda adminisztrátora hamarosan értesítést kap és megtudja, hogy foglalást kértél.</p>

            <div class="booking-details">
                <h3>Foglalás részletei</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Foglalás azonosító:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Szálloda:</span>
                    <span class="detail-value">{{ $booking->hotel->name }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Érkezés:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->startDate)->format('Y-m-d') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Távozás:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->endDate)->format('Y-m-d') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Összeg:</span>
                    <span class="detail-value"><strong>€{{ number_format($booking->totalPrice, 2, ',', ' ') }}</strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Státusz:</span>
                    <span class="detail-value">
                        <span class="status-badge">Függőben</span>
                    </span>
                </div>
            </div>

            <div class="info-box">
                <p><strong>Mi történik most?</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px; color: #1e40af;">
                    <li>A szálloda adminisztrátora értesítést kap a foglalási kérésedről</li>
                    <li>A szálloda hamarosan elfogadja a foglalást</li>
                    <li>Fizetési módodtól függően a számlát és a további információkat e-mailben küldjük</li>
                    <li>Addig is kérjük, hogy türelemmel várj a válaszra</li>
                </ul>
            </div>

            <p>Amint a szálloda elfogadja a foglalást, azonnal értesítünk!</p>
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
