<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalás megerősítve</title>
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
        h2 {
            color: #1f2937;
            font-size: 1.25rem;
            margin: 20px 0 10px 0;
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
        .qr-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .qr-container img {
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .qr-instruction {
            margin-top: 15px;
            font-weight: 600;
            color: #1f2937;
        }
        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .success-box p {
            margin: 0;
            color: #065f46;
            font-weight: 600;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
            color: #4b5563;
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
            <h2>Kedves {{ $booking->user->name }}!</h2>

            <div class="success-box">
                <p>✓ Foglalásod megerősítve!</p>
            </div>

            <p>Örömmel értesítünk, hogy a foglalásodat megerősítették a <strong>{{ $booking->hotel->name ?? $booking->rooms->first()->hotel->name ?? 'szállodában' }}</strong>!</p>

            <div class="booking-details">
                <h3>Foglalás részletei</h3>
                
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
                
                @if($booking->rooms && $booking->rooms->count() > 0)
                <div class="detail-row">
                    <span class="detail-label">Foglalt szobák:</span>
                    <span class="detail-value"></span>
                </div>
                @endif
            </div>

            @if($booking->rooms && $booking->rooms->count() > 0)
            <p><strong>Foglalt szobák:</strong></p>
            <ul>
                @foreach($booking->rooms as $room)
                    <li>{{ $room->name }} @if($room->capacity) ({{ $room->capacity }} fős) @endif</li>
                @endforeach
            </ul>
            @endif

            @if($booking->services && $booking->services->count() > 0)
            <p><strong>Szolgáltatások:</strong></p>
            <ul>
                @foreach($booking->services as $service)
                    <li>{{ $service->name }} @if($service->price) – €{{ number_format($service->price, 2, ',', ' ') }} @endif</li>
                @endforeach
            </ul>
            @endif

            <div class="qr-container">
                <p class="qr-instruction">Check-in QR kód</p>
                <p style="font-size: 0.875rem; color: #6b7280; margin-top: 10px;">
                    Az alábbi QR-kódot mutasd fel a check-in során:
                </p>
                <img src="{{ $message->embed($qrPath) }}" alt="QR Code" />
                <p style="font-size: 0.8rem; color: #6b7280; margin-top: 15px;">
                    Check-in token: <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">{{ $booking->checkInToken }}</code>
                </p>
            </div>

            <p><strong>Fontos információk:</strong></p>
            <ul>
                <li>Kérjük, hogy érkezéskor mutasd fel ezt a QR kódot a recepción</li>
                <li>A QR kód csak erre a foglalásra érvényes</li>
                <li>Ha bármilyen kérdésed van, vedd fel a kapcsolatot a szállodával</li>
            </ul>
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
