<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>artisanskikda | Nouveau mot de passe</title>
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
                <span class="section-tag">Sécurité</span>
                <h1 style="margin-top: 16px;">Choisissez un nouveau mot de passe</h1>
                <p class="modal-intro">
                    Entrez votre email et votre nouveau mot de passe. Une confirmation vous sera envoyée par email après validation.
                </p>

                @if ($errors->any())
                    <p class="feedback-message feedback-message--error">
                        {{ $errors->first() }}
                    </p>
                @endif

                <form method="POST" action="{{ route('password.store') }}" class="stack-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        placeholder="Votre email"
                        class="form-control"
                        required
                    >

                    <input
                        type="password"
                        name="password"
                        placeholder="Nouveau mot de passe"
                        class="form-control"
                        required
                    >

                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Confirmer le nouveau mot de passe"
                        class="form-control"
                        required
                    >

                    <button type="submit" class="button button--primary button--block">
                        Mettre à jour le mot de passe
                    </button>
                </form>

                <p class="modal-switch">
                    Retour à l’accueil :
                    <a href="{{ route('home') }}">Se connecter</a>
                </p>
            </article>
        </div>
    </main>
</body>
</html>
