<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA helyreállítás</title>
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
            font-size: 1.6rem;
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
            margin: 26px 0;
        }
        .action-button {
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
        .action-button:hover {
            transform: translateY(-2px);
        }
        .alternative-link {
            margin-top: 18px;
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
            <div class="logo">🔐</div>
            <h1>HotelFlow</h1>
        </div>

        <div class="content">
            <p><strong>Kedves {{ $user->name }}!</strong></p>

            <p>2FA (kétfaktoros hitelesítés) helyreállítási kérést kaptunk a HotelFlow fiókodhoz.</p>

            <p>Ha elvesztetted a telefonod vagy nem férsz hozzá az autentikációs alkalmazáshoz, az alábbi gombbal tudod a 2FA-t helyreállítani (és szükség esetén újra beállítani).</p>

            <div class="button-container">
                <a href="{{ $recoveryUrl }}" class="action-button" style="color: #ffffff;">
                    2FA helyreállítása
                </a>
            </div>

            <p>Ha a gomb nem működik, másold be az alábbi linket a böngésződ címsorába:</p>

            <div class="alternative-link">
                {{ $recoveryUrl }}
            </div>

            <div class="warning">
                <p><strong>Fontos:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Ez a link 30 percig érvényes.</li>
                    <li>Ha nem te kérted a helyreállítást, hagyd figyelmen kívül ezt az üzenetet.</li>
                    <li>Biztonsági okból a helyreállítás véglegesítéséhez a jelszavadra is szükség lesz.</li>
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

