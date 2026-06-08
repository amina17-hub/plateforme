<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>artisanskikda | Mot de passe modifié</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home-professional.css') }}">
</head>
<body class="home-page">
    <div class="page-orb page-orb--teal" aria-hidden="true"></div>
    <div class="page-orb page-orb--sand" aria-hidden="true"></div>

    <main class="section-block">
        <div class="container" style="max-width: 560px;">
            <article class="surface-card surface-card--form">
                <span class="section-tag section-tag--success">Confirmation</span>
                <h1 style="margin-top: 16px;">Mot de passe mis à jour</h1>
                <p class="modal-intro">
                    {{ session('status', 'Votre mot de passe a été mis à jour.') }}
                </p>
                <p class="modal-intro">
                    Un email de confirmation a été envoyé à l’adresse concernée.
                </p>

                <a href="{{ route('home', ['login' => 1, 'password_reset' => 1]) }}" class="button button--primary button--block">
                    Retourner à la connexion
                </a>
            </article>
        </div>
    </main>
</body>
</html>
