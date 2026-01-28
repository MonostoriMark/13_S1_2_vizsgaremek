<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalás megerősítve – fizetésre vár</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f7fa; }
        .email-container { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 2rem; margin-bottom: 10px; }
        h1 { color: #667eea; font-size: 1.75rem; margin: 0 0 10px 0; }
        h2 { color: #1f2937; font-size: 1.25rem; margin: 20px 0 10px 0; }
        p { margin-bottom: 15px; color: #4b5563; }
        .info-box { background: #fff3cd; border-left: 4px solid #f39c12; border-radius: 8px; padding: 15px; margin: 20px 0; }
        .info-box p { margin: 0; color: #856404; font-weight: 600; }
        .booking-details { background: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #6b7280; }
        .detail-value { color: #1f2937; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🏨</div>
            <h1>HotelFlow</h1>
        </div>

        <div class="content">
            <h2>Kedves {{ $booking->user->name }}!</h2>

            <div class="info-box">
                <p>✓ A foglalásodat a szálloda megerősítette. A check-in QR kódot a fizetés beérkezése után küldjük.</p>
            </div>

            <p>A számlát hamarosan elküldjük e-mailben. Kérjük, a fizetést banki átutalással teljesítsd a számla alapján.</p>

            <div class="booking-details">
                <div class="detail-row">
                    <span class="detail-label">Foglalás azonosító:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Szálloda:</span>
                    <span class="detail-value">{{ $booking->hotel->name ?? $booking->rooms->first()->hotel->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Érkezés:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->startDate)->format('Y-m-d') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Távozás:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->endDate)->format('Y-m-d') }}</span>
                </div>
            </div>

            <p>Amint a fizetést a szálloda visszaigazolja, elküldjük a check-in QR kódot, amire érkezéskor szükséged lesz.</p>
        </div>

        <div class="footer">
            <p>Üdvözlettel,<br><strong>HotelFlow csapat</strong></p>
            <p style="margin-top: 15px; font-size: 0.8rem;">Ez egy automatikus üzenet, kérjük ne válaszolj rá.</p>
        </div>
    </div>
</body>
</html>

