<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szálloda létrehozva</title>
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
        .info-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin: 20px 0;
            background: #eff6ff;
            color: #1e40af;
        }
        .content {
            margin-bottom: 30px;
        }
        p {
            margin-bottom: 15px;
            color: #4b5563;
        }
        .hotel-info {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .hotel-info strong {
            color: #111827;
        }
        .important-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .important-box p {
            margin: 0;
            color: #92400e;
            font-size: 0.9rem;
        }
        .email-box {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 6px;
            font-family: monospace;
            color: #1f2937;
            margin: 10px 0;
            word-break: break-all;
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
            <p><strong>Kedves {{ $user->name }}!</strong></p>

            <div class="info-badge">ℹ️ Szálloda létrehozva</div>
            
            <p>Köszönjük, hogy létrehozta a <strong>{{ $hotel->name }}</strong> szállodát a HotelFlow rendszerben!</p>

            <div class="hotel-info">
                <p><strong>Szálloda neve:</strong> {{ $hotel->name }}</p>
                <p><strong>Helyszín:</strong> {{ $hotel->location }}</p>
                <p><strong>Típus:</strong> {{ $hotel->type ?? 'Nincs megadva' }}</p>
                @if($hotel->starRating)
                    <p><strong>Csillagok:</strong> {{ $hotel->starRating }} ⭐</p>
                @endif
            </div>

            <div class="important-box">
                <p><strong>Fontos információ:</strong></p>
                <p>A rendszergazda hamarosan áttekinti és jóváhagyja a szállodáját. Amíg a jóváhagyás nem történik meg, a szálloda nem jelenik meg a keresési eredményekben.</p>
            </div>

            <p><strong>Kérjük, küldje el a szükséges dokumentumokat</strong> a következő e-mail címre a jóváhagyás elősegítése érdekében:</p>

            <div class="email-box">
                {{ $adminEmail }}
            </div>

            <p>Szükséges dokumentumok:</p>
            <ul style="color: #4b5563; margin: 20px 0; padding-left: 20px;">
                <li>Szálloda működési engedélye</li>
                <li>Cégjegyzék kivonat</li>
                <li>Adószám igazolás</li>
                <li>Egyéb releváns dokumentumok</li>
            </ul>

            <p>Miután a dokumentumokat elküldte és a rendszergazda jóváhagyta a szállodát, értesítést kap e-mailben.</p>
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
