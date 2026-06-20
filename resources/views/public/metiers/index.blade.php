<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>artisanskikda | Accueil</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home-professional.css') }}">
</head>
<body class="home-page">
    <div class="page-orb page-orb--teal" aria-hidden="true"></div>
    <div class="page-orb page-orb--sand" aria-hidden="true"></div>

    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand__mark" aria-hidden="true">
                    <img src="{{ asset('images/artisanskikda-logo.svg') }}" alt="" class="brand__logo">
                </span>
                <span class="brand__text">
                    <span class="brand__eyebrow">Plateforme locale</span>
                    <span class="brand__name">artisanskikda</span>
                </span>
            </a>

            <nav class="main-nav" aria-label="Navigation principale">
                <a href="#hero">Accueil</a>
                <a href="#search-tools">Recherche</a>
                <a href="#contact-section">Contact</a>
            </nav>

            <div class="site-header__actions">
                <button id="btn-login" type="button" class="button button--primary button--compact">
                    Se connecter
                </button>
            </div>
        </div>
    </header>

    <main>
        <section id="hero" class="hero-section">
            <div class="container hero-section__grid">
                <div class="hero-copy">
                    <span class="section-tag section-tag--light">Artisans de confiance à Skikda</span>
                    <h1>Trouvez l’artisan qu’il vous faut, sans perdre de temps.</h1>
                    <p class="hero-copy__lead">
                        Recherchez par métier ou par commune, découvrez les profils disponibles
                        et contactez plus vite le bon professionnel près de chez vous.
                    </p>

                    <div class="hero-copy__actions">
                        <a href="#search-tools" class="button button--accent">Trouver un artisan</a>
                        <button id="hero-register" type="button" class="button button--secondary">
                            Créer un compte
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section id="search-tools" class="section-block">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <span class="section-tag">Recherche</span>
                        <h2>Trouvez un artisan selon votre besoin</h2>
                    </div>
                    <p>
                        Deux recherches sont proposées pour rendre l’accès plus direct:
                        par commune pour trouver autour d’une zone, ou par métier pour cibler un service précis.
                    </p>
                </div>

                <div class="search-grid">
                    <article class="search-card search-card--light">
                        <div class="search-card__icon" aria-hidden="true">📍</div>
                        <h3>Recherche par commune</h3>
                        <p>Choisissez une zone et affichez les artisans disponibles autour de cette localisation.</p>

                        <label for="communeInput" class="field-label">Commune</label>
                        <select id="communeInput" class="form-control">
                            <option value="">Choisir une commune</option>
                            @foreach($communes as $commune)
                                <option value="{{ $commune }}">{{ $commune }}</option>
                            @endforeach
                        </select>

                        <button id="btnCommune" type="button" class="button button--primary button--block">
                            Rechercher par commune
                        </button>
                    </article>

                    <article class="search-card search-card--dark">
                        <div class="search-card__icon search-card__icon--dark" aria-hidden="true">🛠️</div>
                        <h3>Recherche par métier</h3>
                        <p>Saisissez directement le type de service pour obtenir des résultats plus ciblés.</p>

                        <label for="jobInput" class="field-label field-label--light">Métier</label>
                        <input
                            id="jobInput"
                            type="text"
                            list="jobs"
                            placeholder="Ex: Plombier, Électricien..."
                            class="form-control form-control--dark"
                        >
                        <datalist id="jobs">
                            @foreach($categories as $category)
                                @foreach($category->metiers as $metier)
                                    <option value="{{ $metier->name }}">{{ $metier->name }}</option>
                                @endforeach
                            @endforeach
                        </datalist>

                        <button id="btnJob" type="button" class="button button--light button--block">
                            Rechercher par métier
                        </button>
                    </article>
                </div>
            </div>
        </section>

        <section id="artisansSection" class="section-block hidden">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <span class="section-tag section-tag--success">Résultats</span>
                        <h2>Artisans disponibles</h2>
                    </div>
                    <p>Les résultats de recherche apparaissent ici.</p>
                </div>

                <div id="artisanResults" class="artisan-grid"></div>
                <div id="artisanPagination" class="artisan-pagination hidden" aria-label="Pagination des résultats"></div>
            </div>
        </section>

        <section id="contact-section" class="section-block">
            <div class="container contact-grid">
                <article class="contact-panel">
                    <span class="section-tag section-tag--light">Contact</span>
                    <h2>Besoin d’aide ou d’informations ?</h2>
                    <p>
                        Cette zone peut servir de point de contact principal pour les utilisateurs
                        qui souhaitent poser une question, proposer un besoin spécifique ou demander un accompagnement.
                    </p>

                    <div class="contact-highlights">
                        <div>
                            <strong>Réponse claire</strong>
                            <span>Une page plus sérieuse inspire plus de confiance.</span>
                        </div>
                        <div>
                            <strong>Parcours plus net</strong>
                            <span>Les actions importantes sont visibles immédiatement.</span>
                        </div>
                    </div>
                </article>

                <article class="surface-card surface-card--form">
                    <form id="contactForm" action="{{ route('home.contact') }}" method="POST" class="stack-form">
                        @csrf
                        <input type="text" name="name" placeholder="Votre nom" class="form-control" required>
                        <input type="email" name="email" placeholder="Votre email" class="form-control" required>
                        <textarea name="message" placeholder="Votre message" rows="5" class="form-control" required></textarea>
                        <button type="submit" class="button button--primary button--block">
                            Envoyer le message
                        </button>
                    </form>

                    <p id="contact-msg" class="feedback-message hidden"></p>
                </article>
            </div>
        </section>
    </main>

    <div id="loginModal" class="site-modal hidden">
        <div class="modal-card modal-card--small">
            <button type="button" class="modal-close close-login" aria-label="Fermer">&times;</button>
            <span class="section-tag">Connexion</span>
            <h2>Accédez à votre espace</h2>
            <p class="modal-intro">Connectez-vous pour poursuivre votre recherche ou gérer votre activité.</p>

            <form id="loginForm" class="stack-form">
                @csrf
                <input type="email" name="email" placeholder="Email" class="form-control" required>
                <input type="password" name="password" placeholder="Mot de passe" class="form-control" required>
                <button type="submit" class="button button--primary button--block">
                    Se connecter
                </button>
            </form>

            <p id="login-error-msg" class="feedback-message feedback-message--error"></p>
            <p class="modal-switch">
                <button type="button" id="showForgotPassword">Mot de passe oublié ?</button>
            </p>
            <p class="modal-switch">
                Pas encore inscrit ?
                <button type="button" id="showRegister">Créer un compte</button>
            </p>
        </div>
    </div>

    <div id="forgotPasswordModal" class="site-modal hidden">
        <div class="modal-card modal-card--small">
            <button type="button" class="modal-close close-forgot-password" aria-label="Fermer">&times;</button>
            <span class="section-tag">Réinitialisation</span>
            <h2>Réinitialiser le mot de passe</h2>
            <p class="modal-intro">Entrez votre email. Nous allons envoyer un lien sécurisé de changement de mot de passe.</p>

            <form id="forgotPasswordForm" class="stack-form">
                @csrf
                <input type="email" name="email" placeholder="Votre email" class="form-control" required>
                <button type="submit" class="button button--primary button--block">
                    Envoyer le lien
                </button>
            </form>

            <p id="forgot-password-error-msg" class="feedback-message feedback-message--error"></p>
            <p id="forgot-password-success-msg" class="feedback-message feedback-message--success"></p>
            <p class="modal-switch">
                <button type="button" id="backToLoginFromForgot">Retour à la connexion</button>
            </p>
        </div>
    </div>

    <div id="registerModal" class="site-modal hidden">
        <div class="modal-card">
            <button type="button" class="modal-close close-register" aria-label="Fermer">&times;</button>
            <span class="section-tag section-tag--success">Inscription</span>
            <h2>Créez un compte client ou artisan</h2>
            <p class="modal-intro">Renseignez vos informations et activez votre localisation pour démarrer plus vite.</p>

            <form id="registerForm" action="{{ route('register') }}" method="POST" class="stack-form">
                @csrf

                <div class="form-note">
                    <label for="userType" class="field-label">Je suis</label>
                    <select name="role" id="userType" class="form-control">
                        <option value="client">Client</option>
                        <option value="artisan">Artisan</option>
                    </select>
                </div>

                <div class="location-banner">
                    <div>
                        <span class="section-tag section-tag--success">Position automatique</span>
                        <p id="register-location-status" class="status-text">
                            Détection de votre position en attente.
                        </p>
                    </div>
                    <button
                        id="detect-register-location"
                        type="button"
                        class="button button--success button--compact"
                    >
                        Utiliser ma position
                    </button>
                </div>

                <div class="form-grid">
                    <input type="text" name="name" placeholder="Nom complet" class="form-control" required>

                    <div>
                        <label for="register-commune" class="field-label">Commune</label>
                        <select id="register-commune" name="commune" class="form-control" required>
                            <option value="">Choisir une commune de Skikda</option>
                            @foreach($communes as $commune)
                                <option value="{{ $commune }}">{{ $commune }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="email" name="email" placeholder="Email" class="form-control" required>
                </div>

                <input id="register-city" type="hidden" name="city">
                <input id="register-latitude" type="hidden" name="latitude">
                <input id="register-longitude" type="hidden" name="longitude">

                <div id="artisanFields" class="form-note hidden">
                    <span class="section-tag">Informations artisan</span>
                    <div class="stack-form stack-form--tight">
                        <select name="service_type" class="form-control">
                            <option value="">-- Choisir un type de service --</option>
                            @foreach($categories as $category)
                                @foreach($category->metiers as $metier)
                                    <option value="{{ $metier->name }}">{{ $metier->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                        <textarea
                            name="description"
                            rows="4"
                            placeholder="Description de vos services..."
                            class="form-control"
                        ></textarea>
                    </div>
                </div>

                <input type="password" name="password" placeholder="Mot de passe" class="form-control" required>

                <button type="submit" class="button button--primary button--block">
                    Créer mon compte
                </button>
            </form>

            <p id="register-error-msg" class="feedback-message feedback-message--error"></p>
            <p id="register-success-msg" class="feedback-message feedback-message--success"></p>
            <p class="modal-switch">
                Déjà un compte ?
                <button type="button" id="backToLogin">Retour à la connexion</button>
            </p>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p>&copy; 2026 artisanskikda. Tous droits réservés.</p>
            <div class="site-footer__links">
                <a href="#contact-section">Contact</a>
                <a href="#search-tools">Recherche</a>
            </div>
        </div>
    </footer>

    <script>
    axios.defaults.withCredentials = true;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    axios.defaults.headers.common['Accept'] = 'application/json';

    document.addEventListener('DOMContentLoaded', function () {
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const forgotPasswordModal = document.getElementById('forgotPasswordModal');
        const registerForm = document.getElementById('registerForm');
        const userType = document.getElementById('userType');
        const artisanFields = document.getElementById('artisanFields');
        const artisansSection = document.getElementById('artisansSection');
        const contactForm = document.getElementById('contactForm');
        const contactMsg = document.getElementById('contact-msg');
        const registerLocationStatus = document.getElementById('register-location-status');
        const detectRegisterLocationButton = document.getElementById('detect-register-location');
        const registerCityInput = document.getElementById('register-city');
        const registerCommuneInput = document.getElementById('register-commune');
        const registerLatitudeInput = document.getElementById('register-latitude');
        const registerLongitudeInput = document.getElementById('register-longitude');
        const artisanResults = document.getElementById('artisanResults');
        const artisanPagination = document.getElementById('artisanPagination');
        const skikdaCommunes = Array.from(registerCommuneInput.options)
            .map((option) => option.value)
            .filter(Boolean);
        const artisanResultsPerPage = 6;
        const artisanResultsState = {
            items: [],
            title: '',
            currentPage: 1,
        };
        let registerLocationPending = false;

        function setContactMessage(message, isError) {
            contactMsg.textContent = message;
            contactMsg.className = `feedback-message ${isError ? 'feedback-message--error' : 'feedback-message--success'}`;
        }

        function openModal(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (modal === registerModal) {
                detectRegisterLocation();
            }
        }

        function closeModal(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('btn-login').addEventListener('click', () => openModal(loginModal));
        document.getElementById('hero-register').addEventListener('click', () => openModal(registerModal));
        document.querySelector('.close-login').addEventListener('click', () => closeModal(loginModal));
        document.querySelector('.close-register').addEventListener('click', () => closeModal(registerModal));
        document.querySelector('.close-forgot-password').addEventListener('click', () => closeModal(forgotPasswordModal));

        document.getElementById('showRegister').addEventListener('click', () => {
            closeModal(loginModal);
            openModal(registerModal);
        });

        document.getElementById('showForgotPassword').addEventListener('click', () => {
            closeModal(loginModal);
            openModal(forgotPasswordModal);
        });

        document.getElementById('backToLogin').addEventListener('click', () => {
            closeModal(registerModal);
            openModal(loginModal);
        });

        document.getElementById('backToLoginFromForgot').addEventListener('click', () => {
            closeModal(forgotPasswordModal);
            openModal(loginModal);
        });

        userType.addEventListener('change', function () {
            artisanFields.classList.toggle('hidden', this.value !== 'artisan');
        });

        function setRegisterLocationStatus(message, isError) {
            registerLocationStatus.textContent = message;
            registerLocationStatus.className = isError ? 'status-text is-error' : 'status-text';
        }

        function updateHiddenLocationFields(latitude, longitude, city) {
            registerLatitudeInput.value = latitude ? String(Number(latitude).toFixed(6)) : '';
            registerLongitudeInput.value = longitude ? String(Number(longitude).toFixed(6)) : '';
            registerCityInput.value = city || '';
        }

        function normalizeCommuneName(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/['’]/g, '')
                .trim()
                .toLowerCase();
        }

        function trySelectDetectedCommune(rawCommune) {
            const normalizedDetected = normalizeCommuneName(rawCommune);

            if (!normalizedDetected) {
                return false;
            }

            const matchingCommune = skikdaCommunes.find(function (commune) {
                const normalizedOption = normalizeCommuneName(commune);
                return normalizedOption === normalizedDetected
                    || normalizedOption.includes(normalizedDetected)
                    || normalizedDetected.includes(normalizedOption);
            });

            if (!matchingCommune) {
                return false;
            }

            registerCommuneInput.value = matchingCommune;
            return true;
        }

        async function fillRegisterAddressFromCoordinates(latitude, longitude) {
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}&accept-language=fr`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error('reverse-geocode-failed');
                }

                const data = await response.json();
                const address = data.address || {};
                const detectedCity = address.city || address.town || address.village || address.county || 'Skikda';
                const detectedCommune = address.municipality || address.city_district || address.suburb || address.town || address.village || '';

                updateHiddenLocationFields(latitude, longitude, detectedCity);

                if (trySelectDetectedCommune(detectedCommune)) {
                    setRegisterLocationStatus('Position détectée. Commune sélectionnée automatiquement.', false);
                    return;
                }

                setRegisterLocationStatus('Position détectée. Choisissez votre commune dans la liste.', false);
            } catch (error) {
                updateHiddenLocationFields(latitude, longitude, 'Skikda');
                setRegisterLocationStatus('Latitude et longitude remplies. Choisissez votre commune dans la liste.', false);
            }
        }

        function getRegisterGeolocationErrorMessage(error) {
            const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);

            if (!window.isSecureContext && !isLocalhost) {
                return 'La localisation du navigateur exige HTTPS sur ce domaine.';
            }

            if (!error) {
                return 'Impossible de récupérer votre position.';
            }

            if (error.code === 1) {
                return 'Accès à la position refusé. Autorisez la localisation puis réessayez.';
            }

            if (error.code === 2) {
                return 'Position indisponible pour le moment. Vérifiez votre GPS ou votre connexion.';
            }

            if (error.code === 3) {
                return 'La détection de position a expiré. Réessayez.';
            }

            return 'Impossible de récupérer votre position.';
        }

        function detectRegisterLocation() {
            if (registerLocationPending) {
                return;
            }

            if (!navigator.geolocation) {
                setRegisterLocationStatus('La géolocalisation n’est pas supportée par ce navigateur.', true);
                return;
            }

            registerLocationPending = true;
            detectRegisterLocationButton.disabled = true;
            detectRegisterLocationButton.classList.add('is-loading');
            setRegisterLocationStatus('Détection de votre position en cours...', false);

            navigator.geolocation.getCurrentPosition(
                async function (position) {
                    await fillRegisterAddressFromCoordinates(position.coords.latitude, position.coords.longitude);
                    registerLocationPending = false;
                    detectRegisterLocationButton.disabled = false;
                    detectRegisterLocationButton.classList.remove('is-loading');
                },
                function (error) {
                    registerLocationPending = false;
                    detectRegisterLocationButton.disabled = false;
                    detectRegisterLocationButton.classList.remove('is-loading');
                    setRegisterLocationStatus(getRegisterGeolocationErrorMessage(error), true);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000,
                }
            );
        }

        detectRegisterLocationButton.addEventListener('click', detectRegisterLocation);

        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const errorNode = document.getElementById('login-error-msg');
            errorNode.textContent = '';

            axios.post("{{ route('login') }}", new FormData(this))
                .then((res) => {
                    if (res.data.success) {
                        window.location.href = res.data.redirect;
                        return;
                    }

                    errorNode.textContent = res.data.message || 'Erreur connexion';
                })
                .catch((err) => {
                    errorNode.textContent = (err.response && err.response.data && err.response.data.message) || 'Erreur connexion';
                });
        });

        document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const errorNode = document.getElementById('forgot-password-error-msg');
            const successNode = document.getElementById('forgot-password-success-msg');
            errorNode.textContent = '';
            successNode.textContent = '';

            axios.post("{{ route('password.email') }}", new FormData(this))
                .then((res) => {
                    successNode.textContent = res.data.message || 'Si un compte existe avec cet email, un lien a été envoyé.';
                    this.reset();
                })
                .catch((err) => {
                    errorNode.textContent = (err.response && err.response.data && (err.response.data.message || Object.values(err.response.data.errors || {}).flat()[0])) || 'Erreur serveur';
                });
        });

        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const errorNode = document.getElementById('register-error-msg');
            const successNode = document.getElementById('register-success-msg');
            errorNode.textContent = '';
            successNode.textContent = '';

            if (!registerCityInput.value || !registerLatitudeInput.value || !registerLongitudeInput.value) {
                errorNode.textContent = 'Activez la localisation pour remplir automatiquement la ville et les coordonnées.';
                return;
            }

            if (!registerCommuneInput.value) {
                errorNode.textContent = 'Choisissez votre commune dans la liste.';
                return;
            }

            axios.post(this.action, new FormData(this))
                .then((res) => {
                    if (res.data.success) {
                        successNode.textContent = res.data.message;
                        this.reset();
                        artisanFields.classList.add('hidden');
                        updateHiddenLocationFields('', '', '');
                        setRegisterLocationStatus('Détection de votre position en attente.', false);
                        setTimeout(() => {
                            closeModal(registerModal);
                            openModal(loginModal);
                            successNode.textContent = '';
                        }, 800);
                    }
                })
                .catch((err) => {
                    errorNode.textContent = (err.response && err.response.data && (err.response.data.message || Object.values(err.response.data.errors || {}).flat()[0])) || 'Erreur serveur';
                });
        });

        document.getElementById('btnJob').addEventListener('click', function () {
            const job = document.getElementById('jobInput').value.trim();
            if (!job) {
                return;
            }

            axios.get(`/search/job/${encodeURIComponent(job)}`)
                .then((res) => showArtisans(res.data, `Résultats pour le métier : ${job}`))
                .catch(() => alert('Erreur recherche'));
        });

        document.getElementById('btnCommune').addEventListener('click', function () {
            const commune = document.getElementById('communeInput').value.trim();
            if (!commune) {
                return;
            }

            axios.get(`/search/commune/${encodeURIComponent(commune)}`)
                .then((res) => showArtisans(res.data, `Résultats pour la commune : ${commune}`))
                .catch(() => alert('Erreur recherche'));
        });

        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            setContactMessage('', false);
            contactMsg.classList.add('hidden');

            axios.post(this.action, new FormData(this))
                .then((res) => {
                    if (res.data.success) {
                        setContactMessage(res.data.message, false);
                        contactMsg.classList.remove('hidden');
                        this.reset();
                    }
                })
                .catch((err) => {
                    const message = (err.response && err.response.data && (err.response.data.message || Object.values(err.response.data.errors || {}).flat()[0]))
                        || 'Erreur serveur';

                    setContactMessage(message, true);
                    contactMsg.classList.remove('hidden');
                });
        });

        const searchParams = new URLSearchParams(window.location.search);

        if (searchParams.get('login') === '1' || searchParams.get('password_reset') === '1') {
            openModal(loginModal);
        }

        artisanResults.addEventListener('click', function (event) {
            const profileCard = event.target.closest('[data-profile-login]');

            if (!profileCard) {
                return;
            }

            openModal(loginModal);
        });

        artisanPagination.addEventListener('click', function (event) {
            const pageButton = event.target.closest('[data-page]');

            if (!pageButton || pageButton.disabled) {
                return;
            }

            changeArtisanResultsPage(Number(pageButton.dataset.page));
        });

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value === null || value === undefined ? '' : String(value);
            return div.innerHTML;
        }

        function showArtisans(data, title) {
            artisanResultsState.items = Array.isArray(data) ? data : [];
            artisanResultsState.title = title;
            artisanResultsState.currentPage = 1;

            renderArtisanResults();
            window.scrollTo({ top: artisansSection.offsetTop - 80, behavior: 'smooth' });
        }

        function changeArtisanResultsPage(page) {
            const totalPages = getArtisanResultsTotalPages();

            if (!Number.isFinite(page)) {
                return;
            }

            artisanResultsState.currentPage = Math.min(Math.max(page, 1), totalPages);

            renderArtisanResults();
            window.scrollTo({ top: artisansSection.offsetTop - 80, behavior: 'smooth' });
        }

        function getArtisanResultsTotalPages() {
            return Math.max(1, Math.ceil(artisanResultsState.items.length / artisanResultsPerPage));
        }

        function renderArtisanResults() {
            artisansSection.classList.remove('hidden');
            const titleBlock = artisansSection.querySelector('h2');
            titleBlock.textContent = artisanResultsState.title;

            if (!artisanResultsState.items.length) {
                artisanResults.innerHTML = `
                    <div class="empty-state">
                        <p>Aucun artisan trouvé.</p>
                    </div>
                `;
                artisanPagination.classList.add('hidden');
                artisanPagination.innerHTML = '';
                return;
            }

            const totalPages = getArtisanResultsTotalPages();

            if (artisanResultsState.currentPage > totalPages) {
                artisanResultsState.currentPage = totalPages;
            }

            const startIndex = (artisanResultsState.currentPage - 1) * artisanResultsPerPage;
            const pageItems = artisanResultsState.items.slice(startIndex, startIndex + artisanResultsPerPage);

            artisanResults.innerHTML = pageItems.map((artisan) => `
                    <button type="button" class="artisan-card artisan-card--interactive" data-profile-login>
                        <div class="artisan-card__header">
                            <div>
                                <h3>${escapeHtml(artisan.name || 'Artisan')}</h3>
                                <p class="artisan-card__service">${escapeHtml(artisan.service_type || '')}</p>
                            </div>
                            <span class="artisan-badge">Disponible</span>
                        </div>

                        <div class="artisan-card__meta">
                            <div>
                                <span>Ville</span>
                                <strong>${escapeHtml(artisan.city || '-')}</strong>
                            </div>
                            <div>
                                <span>Commune</span>
                                <strong>${escapeHtml(artisan.commune || '-')}</strong>
                            </div>
                        </div>

                        <p class="artisan-card__description">${escapeHtml(artisan.description || 'Aucune description disponible.')}</p>
                        <span class="artisan-card__login-hint">Connectez-vous pour consulter ce profil</span>
                    </button>
                `).join('');

            renderArtisanPagination(totalPages, startIndex, pageItems.length);
        }

        function renderArtisanPagination(totalPages, startIndex, visibleCount) {
            if (totalPages <= 1) {
                artisanPagination.classList.add('hidden');
                artisanPagination.innerHTML = '';
                return;
            }

            const currentPage = artisanResultsState.currentPage;
            const endIndex = startIndex + visibleCount;
            const pageButtons = getVisibleArtisanPaginationPages(currentPage, totalPages).map(function (page) {
                if (page === 'ellipsis') {
                    return '<span class="artisan-pagination__ellipsis" aria-hidden="true">...</span>';
                }

                const isCurrent = page === currentPage;

                return `
                    <button
                        type="button"
                        class="artisan-pagination__button ${isCurrent ? 'is-active' : ''}"
                        data-page="${page}"
                        ${isCurrent ? 'aria-current="page"' : ''}
                    >
                        ${page}
                    </button>
                `;
            }).join('');

            artisanPagination.classList.remove('hidden');
            artisanPagination.innerHTML = `
                <p class="artisan-pagination__summary">
                    Résultats ${startIndex + 1}-${endIndex} sur ${artisanResultsState.items.length}
                </p>
                <div class="artisan-pagination__controls">
                    <button
                        type="button"
                        class="artisan-pagination__button artisan-pagination__button--wide"
                        data-page="${currentPage - 1}"
                        ${currentPage === 1 ? 'disabled' : ''}
                    >
                        Précédent
                    </button>
                    ${pageButtons}
                    <button
                        type="button"
                        class="artisan-pagination__button artisan-pagination__button--wide"
                        data-page="${currentPage + 1}"
                        ${currentPage === totalPages ? 'disabled' : ''}
                    >
                        Suivant
                    </button>
                </div>
            `;
        }

        function getVisibleArtisanPaginationPages(currentPage, totalPages) {
            if (totalPages <= 7) {
                return Array.from({ length: totalPages }, function (_, index) {
                    return index + 1;
                });
            }

            if (currentPage <= 4) {
                return [1, 2, 3, 4, 5, 'ellipsis', totalPages];
            }

            if (currentPage >= totalPages - 3) {
                return [1, 'ellipsis', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
            }

            return [1, 'ellipsis', currentPage - 1, currentPage, currentPage + 1, 'ellipsis', totalPages];
        }
    });
    </script>
</body>
</html>
