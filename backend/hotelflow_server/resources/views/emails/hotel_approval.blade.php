<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isApproved ? 'Szálloda jóváhagyva' : 'Szálloda elutasítva' }}</title>
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
            color: {{ $isApproved ? '#10b981' : '#ef4444' }};
            font-size: 1.75rem;
            margin: 0 0 10px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin: 20px 0;
            background: {{ $isApproved ? '#d1fae5' : '#fee2e2' }};
            color: {{ $isApproved ? '#059669' : '#dc2626' }};
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
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .admin-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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

            @if($isApproved)
                <div class="status-badge">✅ Szállodája jóváhagyva!</div>
                <p>Örömmel értesítjük, hogy a <strong>{{ $hotel->name }}</strong> szállodája jóváhagyásra került, és mostantól látható a HotelFlow rendszerben.</p>
                <p>Az ügyfelek mostantól foglalhatnak szobákat a szállodájában, és az ajánlatai megjelennek a keresési eredményekben.</p>
                
                <div style="background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0; color: #1e40af; font-size: 0.9rem;"><strong>Fontos:</strong> Kérjük, frissítse a szálloda adatait és töltse ki a számlázási adatokat (adószám, bankszámlaszám, EU adószám) az admin felületen a teljes funkcionalitás érdekében.</p>
                </div>
            @else
                <div class="status-badge">❌ Szállodája elutasítva</div>
                <p>Sajnáljuk, de a <strong>{{ $hotel->name }}</strong> szállodája jelenleg nem került jóváhagyásra.</p>
                <p>Kérjük, lépjen kapcsolatba az ügyfélszolgálattal további információkért.</p>
            @endif

            <div class="hotel-info">
                <p><strong>Szálloda neve:</strong> {{ $hotel->name }}</p>
                <p><strong>Helyszín:</strong> {{ $hotel->location }}</p>
                <p><strong>Típus:</strong> {{ $hotel->type ?? 'Nincs megadva' }}</p>
                @if($hotel->starRating)
                    <p><strong>Csillagok:</strong> {{ $hotel->starRating }} ⭐</p>
                @endif
            </div>

            @if($isApproved)
                <div class="button-container">
                    <a href="{{ $adminUrl }}" class="admin-button" style="color: #ffffff;">
                        Admin felület megnyitása
                    </a>
                </div>
            @endif
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
