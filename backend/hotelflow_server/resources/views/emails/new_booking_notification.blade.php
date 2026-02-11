<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új foglalás érkezett</title>
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
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .view-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s ease;
        }
        .view-button:hover {
            transform: translateY(-2px);
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
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
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
            <p><strong>Kedves {{ $booking->hotel->user->name }}!</strong></p>

            <p>Új foglalás érkezett a <strong>{{ $booking->hotel->name }}</strong> szállodádhoz!</p>

            <div class="booking-details">
                <h3>Foglalás részletei</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Foglalás azonosító:</span>
                    <span class="detail-value">#{{ $booking->id }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Vendég neve:</span>
                    <span class="detail-value">{{ $booking->user->name }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Vendég e-mail:</span>
                    <span class="detail-value">{{ $booking->user->email }}</span>
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
                        <span class="status-badge status-{{ $booking->status }}">
                            @if($booking->status === 'pending')
                                Függőben
                            @elseif($booking->status === 'confirmed')
                                Megerősítve
                            @else
                                {{ $booking->status }}
                            @endif
                        </span>
                    </span>
                </div>
            </div>

            <p>Kérjük, hogy ellenőrizd a foglalás részleteit és válaszolj a kérésre a rendszerben.</p>

            <div class="button-container">
                <a href="{{ $bookingsUrl }}" class="view-button" style="color: #ffffff;">
                    Foglalások megtekintése
                </a>
            </div>

            <p style="font-size: 0.875rem; color: #6b7280;">
                Ha a gomb nem működik, másold be az alábbi linket a böngésződ címsorába:<br>
                <span style="word-break: break-all; color: #667eea;">{{ $bookingsUrl }}</span>
            </p>
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
