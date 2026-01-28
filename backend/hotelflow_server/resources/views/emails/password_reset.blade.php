<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelszó visszaállítás</title>
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
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
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
        .reset-button:hover {
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
        .alternative-link {
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #6b7280;
            word-break: break-all;
        }
        .warning {
            margin-top: 20px;
            padding: 15px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            font-size: 0.875rem;
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
            <p><strong>Kedves {{ $user->name }}!</strong></p>

            <p>Jelszó visszaállítási kérést kaptunk a HotelFlow fiókodhoz.</p>

            <p>Ha te kérted a jelszó visszaállítását, kérjük, hogy kattints az alábbi gombra:</p>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button" style="color: #ffffff;">
                    Jelszó visszaállítása
                </a>
            </div>

            <p>Ha a gomb nem működik, másold be az alábbi linket a böngésződ címsorába:</p>

            <div class="alternative-link">
                {{ $resetUrl }}
            </div>

            <div class="warning">
                <p><strong>Fontos:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Ez a link 60 percig érvényes.</li>
                    <li>Ha nem te kérted a jelszó visszaállítását, hagyd figyelmen kívül ezt az üzenetet.</li>
                    <li>A jelszavad nem változik meg, amíg nem kattintasz a fenti linkre és nem állítod be az új jelszavadat.</li>
                </ul>
            </div>
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
