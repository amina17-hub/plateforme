<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur artisanskikda</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#152131;">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px;">
        <div style="overflow:hidden;border-radius:24px;background:#ffffff;box-shadow:0 14px 40px rgba(21,33,49,0.08);">
            <div style="padding:32px;background:linear-gradient(135deg,#17324a 0%,#0f766e 100%);color:#ffffff;">
                <div style="display:inline-block;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,0.14);font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;">
                    inscription confirmée
                </div>
                <h1 style="margin:18px 0 0;font-size:32px;line-height:1.15;">Bienvenue sur artisanskikda</h1>
                <p style="margin:14px 0 0;font-size:16px;line-height:1.7;color:rgba(255,255,255,0.84);">
                    Bonjour {{ $user->name }}, votre compte {{ $roleLabel }} a bien été créé sur la plateforme.
                </p>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px;font-size:16px;line-height:1.8;">
                    Nous sommes heureux de vous compter parmi les utilisateurs de <strong>artisanskikda</strong>.
                </p>

                <div style="margin:24px 0;padding:20px;border:1px solid #e2e8f0;border-radius:18px;background:#f8fafc;">
                    <p style="margin:0 0 8px;font-size:14px;color:#64748b;">Nom</p>
                    <p style="margin:0 0 14px;font-size:16px;font-weight:700;">{{ $user->name }}</p>

                    <p style="margin:0 0 8px;font-size:14px;color:#64748b;">Email</p>
                    <p style="margin:0 0 14px;font-size:16px;font-weight:700;">{{ $user->email }}</p>

                    <p style="margin:0 0 8px;font-size:14px;color:#64748b;">Type de compte</p>
                    <p style="margin:0;font-size:16px;font-weight:700;text-transform:capitalize;">{{ $roleLabel }}</p>
                </div>

                <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#475569;">
                    Vous pouvez maintenant vous connecter à la plateforme et continuer votre parcours.
                </p>

                <a href="{{ url('/') }}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#d29a2f;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">
                    Accéder à artisanskikda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
