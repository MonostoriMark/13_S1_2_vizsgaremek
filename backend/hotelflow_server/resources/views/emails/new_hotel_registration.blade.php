<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új szálloda regisztráció</title>
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
        .alert-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin: 20px 0;
            background: #fef3c7;
            color: #d97706;
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
            <p><strong>Kedves Super Admin!</strong></p>

            <div class="alert-badge">⚠️ Új szálloda regisztráció</div>
            
            <p>Egy új szálloda regisztrált a HotelFlow rendszerbe, és jóváhagyásra vár.</p>

            <div class="hotel-info">
                <p><strong>Szálloda neve:</strong> {{ $hotel->name }}</p>
                <p><strong>Helyszín:</strong> {{ $hotel->location }}</p>
                <p><strong>Tulajdonos neve:</strong> {{ $user->name }}</p>
                <p><strong>Tulajdonos e-mail:</strong> {{ $user->email }}</p>
                <p><strong>Típus:</strong> {{ $hotel->type ?? 'Nincs megadva' }}</p>
                @if($hotel->starRating)
                    <p><strong>Csillagok:</strong> {{ $hotel->starRating }} ⭐</p>
                @endif
                @if($hotel->description)
                    <p><strong>Leírás:</strong> {{ $hotel->description }}</p>
                @endif
            </div>

            <p><strong>Fontos:</strong> A szálloda jelenleg nem látható a felhasználók számára, amíg jóvá nem hagyja.</p>

            <div class="button-container">
                <a href="{{ $adminUrl }}" class="admin-button" style="color: #ffffff;">
                    Szállodák kezelése
                </a>
            </div>
        </div>

        <div class="footer">
            <p>Üdvözlettel,<br><strong>HotelFlow rendszer</strong></p>
            <p style="margin-top: 15px; font-size: 0.8rem;">
                Ez egy automatikus üzenet, kérjük ne válaszolj rá.
            </p>
        </div>
    </div>
</body>
</html>
