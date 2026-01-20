<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalás törölve</title>
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
            color: #ef4444;
            font-size: 1.6rem;
            margin: 0 0 10px 0;
        }
        p {
            margin-bottom: 15px;
            color: #4b5563;
        }
        .details {
            margin-top: 18px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        .details-row {
            margin: 6px 0;
            color: #374151;
            font-size: 0.95rem;
        }
        .reason {
            margin-top: 18px;
            padding: 15px;
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            color: #7f1d1d;
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
            <div class="logo">📩</div>
            <h1>Foglalás törölve</h1>
        </div>

        <p><strong>Kedves {{ $booking->user->name ?? 'Vendég' }}!</strong></p>

        <p>Értesítünk, hogy a foglalásod törlésre került.</p>

        <div class="details">
            <div class="details-row"><strong>Foglalás azonosító:</strong> #{{ $booking->id }}</div>
            <div class="details-row"><strong>Szálloda:</strong> {{ $booking->hotel->name ?? 'Hotel' }}</div>
            <div class="details-row"><strong>Időszak:</strong> {{ $booking->startDate }} – {{ $booking->endDate }}</div>
        </div>

        @if(!empty($reason))
            <div class="reason">
                <strong>Megjegyzés:</strong> {{ $reason }}
            </div>
        @endif

        <p>Ha kérdésed van, kérjük, vedd fel velünk a kapcsolatot.</p>

        <div class="footer">
            <p>Üdvözlettel,<br><strong>HotelFlow csapat</strong></p>
            <p style="margin-top: 15px; font-size: 0.8rem;">
                Ez egy automatikus üzenet, kérjük ne válaszolj rá.
            </p>
        </div>
    </div>
</body>
</html>

