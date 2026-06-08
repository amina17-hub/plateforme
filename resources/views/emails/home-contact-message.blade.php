<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau message de contact</title>
</head>
<body style="margin:0;padding:24px;background:#f5f7fb;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 12px 30px rgba(15,23,42,0.08);">
        <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#0f766e;">
            artisanskikda
        </p>
        <h1 style="margin:0 0 24px;font-size:24px;line-height:1.3;">
            Nouveau message de contact depuis la page d'accueil
        </h1>

        <p style="margin:0 0 12px;font-size:15px;"><strong>Nom :</strong> {{ $senderName }}</p>
        <p style="margin:0 0 24px;font-size:15px;"><strong>Email :</strong> {{ $senderEmail }}</p>

        <div style="padding:20px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;">
            <p style="margin:0 0 10px;font-size:14px;font-weight:700;">Message</p>
            <p style="margin:0;font-size:15px;line-height:1.7;white-space:pre-line;">{{ $messageBody }}</p>
        </div>
    </div>
</body>
</html>
