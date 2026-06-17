<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="icon" type="image/png" href="/favicon.png">
<title>Espace Client</title>
</head>

<body class="min-h-screen bg-[linear-gradient(180deg,#f7f7f2_0%,#eef6f6_40%,#ffffff_100%)] text-slate-900" style="font-family: 'Manrope', sans-serif;">
<form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
    @csrf
</form>

<main class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 -z-10 h-[440px] bg-[radial-gradient(circle_at_top_left,rgba(13,148,136,0.18),transparent_28%),radial-gradient(circle_at_top_right,rgba(59,130,246,0.16),transparent_24%)]"></div>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <header class="relative z-[1000] mb-6 flex flex-col gap-4 rounded-[30px] border border-white/70 bg-white/80 px-5 py-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur md:flex-row md:items-center md:justify-between md:px-7">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.28em] text-teal-700">Espace client</p>
<h1 class="mt-1 text-2xl font-extrabold text-slate-900">Bonjour {{ auth()->user()->name }}</h1>                
<p class="mt-2 text-sm text-slate-500">Recherchez, contactez et suivez vos demandes artisans depuis un seul espace.</p>
            </div>

            <div class="relative flex flex-wrap items-center gap-3">
                <details class="group relative z-[1001] inline-block text-left">
                    <summary class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-300 hover:text-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-400">
                        Notifications
                        <span id="notifications-count-badge" class="rounded-full bg-rose-600 px-2 py-0.5 text-xs font-extrabold text-white">0</span>
                    </summary>
                    <div class="absolute right-0 z-[1002] mt-2 w-80 rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_18px_40px_rgba(15,23,42,0.14)]">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <p class="text-sm font-extrabold text-slate-900">Notifications</p>
                            <button id="notifications-read-button" type="button" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-teal-300 hover:text-teal-700">Lu</button>
                        </div>
                        <div id="notifications-list" class="grid max-h-80 gap-2 overflow-y-auto pr-1"></div>
                    </div>
                </details>
                <details class="group relative z-[1001] inline-block text-left">
                    <summary class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-400">
                        Menu
                        <svg class="h-4 w-4 text-slate-600 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="absolute right-0 z-[1002] mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-[28px] border border-slate-200 bg-white p-4 shadow-[0_18px_40px_rgba(15,23,42,0.12)]">
                        <button id="client-menu-reservations" type="button" disabled class="mb-3 flex w-full cursor-not-allowed items-center justify-between rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-400 opacity-70">
                            <span>Reservations</span>
                            <span id="reservations-count-badge" class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">0</span>
                        </button>
                        <button type="button" onclick="this.closest('details').removeAttribute('open'); showPreparedRequests()" class="mb-3 flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-800 transition hover:border-slate-300 hover:bg-slate-100">
                            <span>Demandes préparées</span>
                            <span id="requests-count-badge" class="rounded-full bg-slate-900 px-3 py-1 text-xs font-bold text-white">0</span>
                        </button>
                        <details id="profiles-search-history-block" class="group/history mb-3 rounded-2xl border border-slate-200 bg-slate-50">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-slate-800 transition hover:bg-slate-100 [&::-webkit-details-marker]:hidden">
                                <span>Historique des recherches</span>
                                <span class="flex items-center gap-2">
                                    <span id="profiles-search-history-count" class="rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">0</span>
                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open/history:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </summary>
                            <div class="border-t border-slate-200 p-3">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <p class="text-xs text-slate-500">Cliquez sur un terme pour relancer.</p>
                                    <button type="button" id="profiles-search-history-clear" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-slate-300 hover:text-slate-900">Effacer</button>
                                </div>
                                <div id="profiles-search-history-list" class="grid max-h-48 gap-2 overflow-y-auto pr-1"></div>
                            </div>
                        </details>
                        <details id="favorite-artisans-block" class="group/favorites mb-3 rounded-2xl border border-amber-200 bg-amber-50">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-slate-800 transition hover:bg-amber-100 [&::-webkit-details-marker]:hidden">
                                <span>Mes favoris</span>
                                <span class="flex items-center gap-2">
                                    <span id="favorite-artisans-count" class="rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-slate-950">0</span>
                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open/favorites:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </summary>
                            <div id="favorite-artisans-list" class="grid max-h-56 gap-2 overflow-y-auto border-t border-amber-200 p-3 pr-1"></div>
                        </details>
                        <button type="button" onclick="this.closest('details').removeAttribute('open'); document.getElementById('logout-form').submit()" class="flex w-full items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                            Deconnexion
                        </button>
                    </div>
                </details>
            </div>
        </header>

        <section class="mb-6 overflow-x-auto rounded-[28px] border border-white/70 bg-white/85 p-3 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur">
            <div class="flex min-w-max gap-3">
                <button type="button" data-section-button="search" class="rounded-2xl px-4 py-3 text-left transition">
                    <p class="text-sm font-extrabold">Recherche</p>
                    <p class="mt-1 text-xs">Trouver un artisan</p>
                </button>
                <button type="button" data-section-button="map" class="rounded-2xl px-4 py-3 text-left transition">
                    <p class="text-sm font-extrabold">Carte</p>
                    <p class="mt-1 text-xs">Voir les positions</p>
                </button>
                <button type="button" data-section-button="profiles" class="rounded-2xl px-4 py-3 text-left transition">
                    <p class="text-sm font-extrabold">Profils</p>
                    <p class="mt-1 text-xs">Consulter les resultats</p>
                </button>
                <button type="button" data-section-button="reservations" disabled class="cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-left text-slate-400 opacity-70">
                    <p class="text-sm font-extrabold">Reservations</p>
                    <p class="mt-1 text-xs">Planifier un rendez-vous</p>
                </button>
                <button type="button" data-section-button="chat" class="rounded-2xl px-4 py-3 text-left transition">
                    <p class="text-sm font-extrabold">Chat</p>
                    <p class="mt-1 text-xs">Suivre les discussions</p>
                </button>
            </div>
        </section>

        <div id="alerts" class="hidden mt-5 grid gap-3"></div>

        <section id="section-search" class="mt-6">
            <div class="grid gap-6 xl:grid-cols-[1.25fr_0.95fr]">
                <div class="rounded-[32px] bg-slate-950 px-6 py-7 text-white shadow-[0_28px_80px_rgba(15,23,42,0.20)] sm:px-8">
                    <div class="mb-5 inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] text-teal-200">
                        Recherche + contact
                    </div>
                    <h2 class="max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl">
                        Trouvez rapidement un artisan et envoyez votre demande.
                    </h2>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        Le profil artisan s'ouvre maintenant directement depuis le bouton place a cote de Contacter et Voir sur la carte.
                    </p>

                    <div class="mt-8 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p id="hero-results-count" class="text-2xl font-extrabold">0</p>
                            <p class="mt-1 text-sm text-slate-300">resultats trouves</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p id="hero-selected-count" class="text-2xl font-extrabold">0</p>
                            <p class="mt-1 text-sm text-slate-300">artisan selectionne</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p id="hero-position-count" class="text-2xl font-extrabold">...</p>
                            <p class="mt-1 text-sm text-slate-300">position client</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-7">
                    <div class="mb-6 flex items-center gap-3">
                        <span id="geo-indicator" class="inline-flex h-3 w-3 rounded-full bg-slate-300"></span>
                        <p id="geo-status" class="text-sm font-semibold text-slate-700">Geolocalisation en attente</p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="service" class="mb-2 block text-sm font-bold text-slate-800">
                                Service recherche
                            </label>
                            <select id="service" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-teal-600 focus:bg-white focus:ring-4 focus:ring-teal-100">
                                <option value="">Choisissez un service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service }}">{{ $service }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">Position</p>
                                <p id="position-label" class="mt-2 text-sm font-semibold text-slate-900">En attente de geolocalisation</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">Action</p>
                                <p id="action-label" class="mt-2 text-sm font-semibold text-slate-900">Recherche intelligente locale</p>
                            </div>
                        </div>

                        <button
                            id="search-by-service-button"
                            type="button"
                            class="w-full rounded-2xl bg-teal-700 px-5 py-4 text-sm font-extrabold text-white transition hover:bg-teal-800"
                        >
                            Trouver des artisans
                        </button>
                    </div>
                </div>
            </div>

            <div id="search-map-card" class="mt-6 overflow-hidden rounded-[32px] border border-white/70 bg-white/90 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-teal-700">Carte</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Artisans trouves</h3>
                    </div>
                    <span id="map-points-badge" class="rounded-full bg-slate-900 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-white">
                        0 point
                    </span>
                </div>

                <div id="map" class="h-[420px] overflow-hidden rounded-[24px] border border-slate-200 sm:h-[560px] xl:h-[620px]"></div>
            </div>
        </section>

        <section id="section-profiles" class="mt-6 hidden">
            <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-sky-700">Selection</p>
                        <h3 id="results-title" class="mt-1 text-2xl font-extrabold text-slate-900">Profils recommandes</h3>
                    </div>
                    <span id="profiles-count-badge" class="rounded-full bg-sky-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-sky-800">
                        0 profils
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="profiles-search" class="mb-2 block text-sm font-bold text-slate-800">
                            Nom
                        </label>
                        <input
                            id="profiles-search"
                            type="text"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                            placeholder="Ex. Mohamed"
                        >
                    </div>
                    <div>
                        <label for="profiles-service" class="mb-2 block text-sm font-bold text-slate-800">Service</label>
                        <select
                            id="profiles-service"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">Tous les services</option>
                            @foreach($services as $service)
                                <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="profiles-commune" class="mb-2 block text-sm font-bold text-slate-800">Commune</label>
                        <select
                            id="profiles-commune"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">Toutes les communes</option>
                            @foreach($communes as $commune)
                                <option value="{{ $commune }}">{{ $commune }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end">
                    <button
                        id="profiles-reset-filters"
                        type="button"
                        class="inline-flex items-center justify-center rounded-full bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300"
                    >
                        Réinitialiser les filtres
                    </button>
                </div>

                <div id="profiles-results" class="space-y-4"></div>
                <div id="complementary-services-block" class="mt-5"></div>
            </div>
        </section>

        <section id="section-messaging" class="mt-6 hidden">
            <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-violet-700">Messagerie</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Envoyer une demande</h3>
                    </div>
                    <span class="rounded-full bg-violet-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-violet-800">
                        demo active
                    </span>
                </div>

                <div id="messaging-content"></div>
            </div>
        </section>

        <section id="section-chat" class="mt-6 hidden">
            <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-indigo-700">Chat</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Discussions acceptees</h3>
                    </div>
                    <span id="chat-count-badge" class="rounded-full bg-indigo-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-800">
                        0 active
                    </span>
                </div>

                <div id="chat-content"></div>
            </div>
        </section>

        <section id="section-reservations" class="mt-6 hidden">
            <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="rounded-[32px] border border-white/70 bg-white/90 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-emerald-700">Planning client</p>
                            <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Planifier une reservation</h3>
                        </div>
                        <span id="reservation-next-badge" class="rounded-full bg-emerald-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-emerald-800">
                            Aucun rendez-vous
                        </span>
                    </div>

                    <div id="reservations-content"></div>
                </div>

                <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-sky-700">Calendrier</p>
                            <h3 id="calendar-title" class="mt-1 text-2xl font-extrabold text-slate-900">Vos disponibilites</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="calendar-prev-month" type="button" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-sky-300 hover:text-sky-700">
                                Prec.
                            </button>
                            <button id="calendar-next-month" type="button" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-sky-300 hover:text-sky-700">
                                Suiv.
                            </button>
                        </div>
                    </div>

                    <div id="reservations-calendar"></div>
                </div>
            </div>

            <div class="mt-6 rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-violet-700">Suivi</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Reservations enregistrees</h3>
                    </div>
                    <span id="reservations-list-badge" class="rounded-full bg-violet-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-violet-800">
                        0 active
                    </span>
                </div>

                <div id="reservations-list"></div>
            </div>
        </section>

        <section id="section-map" class="hidden"></section>
    </div>
</main>

<div id="profile-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
    <div id="profile-modal-overlay" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

    <div class="relative z-10 w-full max-w-5xl overflow-hidden rounded-[34px] border border-white/60 bg-white shadow-[0_35px_120px_rgba(15,23,42,0.32)]">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-7">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-amber-700">Profil artisan</p>
                <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Fiche du prestataire</h3>
            </div>
            <button
                id="close-profile-modal"
                type="button"
                class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600"
            >
                Fermer
            </button>
        </div>

        <div id="profile-modal-body" class="max-h-[80vh] overflow-y-auto px-5 py-5 sm:px-7 sm:py-6"></div>
    </div>
</div>

<div id="image-preview-modal" class="fixed inset-0 z-[60] hidden items-center justify-center px-4 py-6">
    <div id="image-preview-overlay" class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm"></div>
    <button id="image-preview-close" type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20">
        Fermer
    </button>
    <figure class="relative z-10 mx-auto max-h-full max-w-5xl">
        <img id="image-preview-img" src="" alt="" class="max-h-[82vh] w-auto max-w-full rounded-[28px] object-contain shadow-[0_28px_80px_rgba(15,23,42,0.45)]">
        <figcaption id="image-preview-caption" class="mt-4 text-center text-sm font-semibold text-white/90"></figcaption>
    </figure>
</div>

<script>
const recommendUrl = @json(route('artisan.recommend'));
const searchArtisansByNameUrl = @json(route('artisans.search.name'));
const artisanWorksUrlBase = @json(url('/artisan/works'));
const artisanProfileBaseUrl = @json(url('/artisans/profile'));
const artisanRatingsBaseUrl = @json(url('/artisans'));
const clientReservationsIndexUrl = @json(route('client.reservations.index'));
const clientReservationsStoreUrl = @json(route('client.reservations.store'));
const clientReservationsDestroyBaseUrl = @json(url('/client/reservations'));
const notificationsIndexUrl = @json(route('notifications.index'));
const notificationsReadUrl = @json(route('notifications.read'));
const clientArtisanRequestsStoreUrl = @json(route('client.artisan-requests.store'));
const artisanAvailabilityBaseUrl = @json(url('/artisans'));
const currentUser = @json($currentUserName);
const currentUserId = @json(auth()->id());
const requestsStorageKey = "artisan_match_requests";
const chatsStorageKey = "artisan_match_chats";
const profilesSearchHistoryStorageKey = "profiles_search_history";
const favoriteArtisansStorageKey = "favorite_artisans";

const state = {
    artisans: [],
    position: null,
    geoPending: false,
    isLoading: false,
    error: "",
    success: "",
    selectedArtisan: null,
    profilePreviewArtisan: null,
    activeSection: "search",
    resultsTitle: "Profils recommandes",
    searchSummary: "Recherche intelligente locale",
    activeServiceType: "",
    recommendedArtisans: [],
    profilesSearchQuery: "",
    profilesSearchHistory: [],
    favoriteArtisans: [],
    profilesSearchService: "",
    profilesSearchCommune: "",
    profilesSearchMinPrice: "",
    profilesSearchMaxPrice: "",
    profilesSearchMinRating: "",
    profilesSearchMaxRating: "",
    profilesSearchLoading: false,
    messageText: "",
    requestsCount: 0,
    acceptedRequests: [],
    activeChatId: "",
    chatMessages: [],
    pendingChatImageDataUrl: "",
    pendingChatImageName: "",
    pendingChatVoiceDataUrl: "",
    pendingChatVoiceMimeType: "",
    chatRecording: false,
    profileWorks: [],
    profileWorksLoading: false,
    profileRatingLoading: false,
    profileRatingSummary: null,
    pendingProfileRating: 0,
    pendingProfileRatingComment: "",
    complementaryServices: [],
    showComplementaryServices: false,
    reservations: [],
    reservationDate: "",
    reservationTime: "10:00",
    reservationNotes: "",
    calendarCursor: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    selectedArtisanAvailability: {},
    selectedArtisanAvailabilityFor: null,
    availabilityLoading: false,
    notifications: [],
    unreadNotificationsCount: 0,
};

const dom = {};
let map = null;
let mapMarkers = [];
let clientMarker = null;
let geolocationRequest = null;
let clientChatRecorder = null;
let clientChatStream = null;
let clientChatChunks = [];
let clientChatRecordingTimeout = null;
let profilesSearchTimeout = null;
let profilesSearchRequestId = 0;

function firstDefined() {
    for (let index = 0; index < arguments.length; index += 1) {
        const value = arguments[index];
        if (value !== null && value !== undefined) {
            return value;
        }
    }

    return null;
}

function haversineDistance(lat1, lon1, lat2, lon2) {
    const toRadians = function (value) {
        return (value * Math.PI) / 180;
    };

    const earthRadiusKm = 6371;
    const deltaLat = toRadians(lat2 - lat1);
    const deltaLon = toRadians(lon2 - lon1);
    const start = toRadians(lat1);
    const end = toRadians(lat2);

    const a = (Math.sin(deltaLat / 2) ** 2)
        + (Math.cos(start) * Math.cos(end) * Math.sin(deltaLon / 2) ** 2);

    return earthRadiusKm * 2 * Math.asin(Math.sqrt(a));
}

function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function getCsrfToken() {
    const element = document.querySelector('meta[name="csrf-token"]');
    return element ? element.getAttribute("content") : "";
}

function getApiErrorMessage(payload, fallbackMessage) {
    if (payload && typeof payload.message === "string" && payload.message.trim()) {
        return payload.message;
    }

    if (payload && payload.errors && typeof payload.errors === "object") {
        const firstError = Object.values(payload.errors)[0];
        if (Array.isArray(firstError) && firstError[0]) {
            return firstError[0];
        }
    }

    return fallbackMessage;
}

async function fetchJson(url, options, fallbackMessage) {
    const requestOptions = options || {};
    const headers = {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(requestOptions.headers || {}),
    };

    if (requestOptions.method && requestOptions.method !== "GET") {
        headers["X-CSRF-TOKEN"] = getCsrfToken();
    }

    const response = await fetch(url, {
        ...requestOptions,
        headers: headers,
    });

    const payload = await response.json().catch(function () {
        return null;
    });

    if (!response.ok) {
        throw new Error(getApiErrorMessage(payload, fallbackMessage));
    }

    return payload;
}

function formatChatTimestamp(value) {
    if (!value) {
        return "";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "";
    }

    return date.toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    });
}

function normalizeChatMessage(message) {
    return {
        id: firstDefined(message?.id, "msg_" + Date.now()),
        sender: firstDefined(message?.sender, "client"),
        senderName: firstDefined(message?.senderName, "Utilisateur"),
        text: String(firstDefined(message?.text, "")),
        imageDataUrl: firstDefined(message?.imageDataUrl, message?.imageUrl, ""),
        imageName: firstDefined(message?.imageName, ""),
        voiceDataUrl: firstDefined(message?.voiceDataUrl, message?.audioDataUrl, ""),
        voiceMimeType: firstDefined(message?.voiceMimeType, message?.audioMimeType, "audio/webm"),
        createdAt: firstDefined(message?.createdAt, new Date().toISOString()),
    };
}

function renderChatMessage(message, outgoingSender) {
    const entry = normalizeChatMessage(message);
    const isOutgoing = entry.sender === outgoingSender;
    const wrapperClasses = isOutgoing ? "justify-end" : "justify-start";
    const bubbleClasses = isOutgoing
        ? "rounded-[22px] rounded-br-md bg-[linear-gradient(135deg,#0f766e_0%,#14b8a6_100%)] text-white shadow-[0_14px_30px_rgba(13,148,136,0.28)]"
        : "rounded-[22px] rounded-bl-md border border-slate-200 bg-white text-slate-800 shadow-[0_10px_24px_rgba(15,23,42,0.08)]";
    const badgeClasses = isOutgoing
        ? "order-2 bg-teal-100 text-teal-800"
        : "bg-slate-200 text-slate-700";
    const timestampClasses = isOutgoing ? "text-teal-100/80" : "text-slate-400";
    const safeText = escapeHtml(entry.text).replace(/\n/g, "<br>");
    const initial = escapeHtml((entry.senderName || "U").trim().charAt(0).toUpperCase());

    return `
        <div class="flex ${wrapperClasses} gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-extrabold ${badgeClasses}">
                ${initial}
            </div>
            <div class="max-w-[85%] px-4 py-3 text-sm ${bubbleClasses}">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] ${isOutgoing ? "text-teal-100/90" : "text-slate-400"}">${escapeHtml(entry.senderName)}</p>
                ${entry.text ? `<p class="mt-1 leading-6">${safeText}</p>` : ""}
                ${entry.imageDataUrl ? `<img src="${entry.imageDataUrl}" alt="${escapeHtml(entry.imageName || "Image envoyee")}" class="mt-3 max-h-72 w-full rounded-2xl object-cover ring-1 ring-white/20">` : ""}
                ${entry.voiceDataUrl ? `
                <div class="mt-3 rounded-2xl ${isOutgoing ? "bg-white/10" : "bg-slate-50"} p-2">
                    <audio controls class="w-full max-w-xs">
                        <source src="${entry.voiceDataUrl}" type="${escapeHtml(entry.voiceMimeType || "audio/webm")}">
                        Votre navigateur ne supporte pas l'audio.
                    </audio>
                </div>
                ` : ""}
                ${entry.createdAt ? `<p class="mt-2 text-[11px] ${timestampClasses}">${escapeHtml(formatChatTimestamp(entry.createdAt))}</p>` : ""}
            </div>
        </div>
    `;
}

function clearPendingChatAttachments() {
    state.pendingChatImageDataUrl = "";
    state.pendingChatImageName = "";
    state.pendingChatVoiceDataUrl = "";
    state.pendingChatVoiceMimeType = "";
}

function persistClientChats(chats) {
    try {
        localStorage.setItem(chatsStorageKey, JSON.stringify(chats));
        return true;
    } catch (error) {
        state.error = "Impossible d'enregistrer cette piece jointe. Essayez un fichier plus leger.";
        state.success = "";
        renderAlerts();
        return false;
    }
}

function readBlobAsDataUrl(blob) {
    return new Promise(function (resolve, reject) {
        const reader = new FileReader();
        reader.onload = function () {
            resolve(String(reader.result || ""));
        };
        reader.onerror = function () {
            reject(new Error("Impossible de lire le fichier selectionne."));
        };
        reader.readAsDataURL(blob);
    });
}

async function resizeImageFileToDataUrl(file) {
    const originalDataUrl = await readBlobAsDataUrl(file);

    return new Promise(function (resolve, reject) {
        const image = new Image();

        image.onload = function () {
            const maxDimension = 1280;
            let width = image.width;
            let height = image.height;

            if (width > maxDimension || height > maxDimension) {
                const scale = Math.min(maxDimension / width, maxDimension / height);
                width = Math.round(width * scale);
                height = Math.round(height * scale);
            }

            const canvas = document.createElement("canvas");
            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext("2d");

            if (!context) {
                reject(new Error("Impossible de preparer l'image."));
                return;
            }

            context.drawImage(image, 0, 0, width, height);
            const mimeType = file.type === "image/png" ? "image/png" : "image/jpeg";
            resolve(canvas.toDataURL(mimeType, 0.82));
        };

        image.onerror = function () {
            reject(new Error("Impossible de charger l'image."));
        };

        image.src = originalDataUrl;
    });
}

async function onClientChatImageSelected(event) {
    const file = event.target.files && event.target.files[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith("image/")) {
        state.error = "Selectionnez une image valide.";
        state.success = "";
        renderAlerts();
        event.target.value = "";
        return;
    }

    try {
        state.pendingChatImageDataUrl = await resizeImageFileToDataUrl(file);
        state.pendingChatImageName = file.name;
        state.error = "";
        state.success = "Photo prete a etre envoyee.";
        renderAll();
    } catch (error) {
        state.error = error.message || "Impossible de preparer cette image.";
        state.success = "";
        renderAlerts();
    } finally {
        event.target.value = "";
    }
}

function stopClientChatStream() {
    if (clientChatStream) {
        clientChatStream.getTracks().forEach(function (track) {
            track.stop();
        });
    }

    clientChatStream = null;
}

async function startClientVoiceRecording() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder === "undefined") {
        state.error = "L'enregistrement vocal n'est pas pris en charge sur cet appareil.";
        state.success = "";
        renderAlerts();
        return;
    }

    try {
        clientChatStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        clientChatChunks = [];
        clientChatRecorder = new MediaRecorder(clientChatStream);
        state.chatRecording = true;
        state.error = "";
        state.success = "Enregistrement vocal en cours...";

        clientChatRecorder.addEventListener("dataavailable", function (event) {
            if (event.data && event.data.size > 0) {
                clientChatChunks.push(event.data);
            }
        });

        clientChatRecorder.addEventListener("stop", async function () {
            const audioBlob = new Blob(clientChatChunks, {
                type: clientChatRecorder && clientChatRecorder.mimeType ? clientChatRecorder.mimeType : "audio/webm",
            });

            clearTimeout(clientChatRecordingTimeout);
            clientChatRecordingTimeout = null;
            stopClientChatStream();
            state.chatRecording = false;

            if (audioBlob.size > 0) {
                try {
                    state.pendingChatVoiceDataUrl = await readBlobAsDataUrl(audioBlob);
                    state.pendingChatVoiceMimeType = audioBlob.type || "audio/webm";
                    state.success = "Message vocal pret a etre envoye.";
                    state.error = "";
                } catch (error) {
                    state.error = error.message || "Impossible de preparer le message vocal.";
                    state.success = "";
                }
            }

            clientChatRecorder = null;
            renderAll();
        });

        clientChatRecorder.start();
        clientChatRecordingTimeout = window.setTimeout(function () {
            if (clientChatRecorder && clientChatRecorder.state === "recording") {
                clientChatRecorder.stop();
            }
        }, 60000);

        renderAll();
    } catch (error) {
        stopClientChatStream();
        state.chatRecording = false;
        state.error = "Impossible d'acceder au microphone.";
        state.success = "";
        renderAlerts();
    }
}

function toggleClientVoiceRecording() {
    if (clientChatRecorder && clientChatRecorder.state === "recording") {
        clientChatRecorder.stop();
        return;
    }

    startClientVoiceRecording();
}

function getArtisanProfileUrl(reference, artisanName) {
    const safeReference = reference !== null && reference !== undefined && reference !== "" ? reference : 0;
    const params = new URLSearchParams();

    if (artisanName) {
        params.set("name", artisanName);
    }

    const query = params.toString();
    return artisanProfileBaseUrl + "/" + safeReference + (query ? "?" + query : "");
}

function normalizeArtisan(artisan) {
    const latitude = Number(artisan.latitude);
    const longitude = Number(artisan.longitude);
    const distanceFromClient = state.position
        ? haversineDistance(state.position.lat, state.position.lng, latitude, longitude)
        : null;

    return {
        artisan_id: firstDefined(artisan.id, artisan.artisan_id, Date.now() + Math.random()),
        profile_reference: firstDefined(artisan.id, artisan.artisan_id, artisan.external_artisan_id),
        external_artisan_id: firstDefined(artisan.external_artisan_id, null),
        user_id: firstDefined(artisan.user_id, null),
        artisan_name: firstDefined(artisan.artisan_name, artisan.name, "Artisan"),
        service_type: firstDefined(artisan.service_type, "Service non renseigne"),
        description: firstDefined(artisan.description, ""),
        commune: firstDefined(artisan.commune, artisan.city, ""),
        city: firstDefined(artisan.city, ""),
        latitude: latitude,
        longitude: longitude,
        price: firstDefined(artisan.price, "-"),
        rating: firstDefined(artisan.rating, "-"),
        distance_km: firstDefined(
            artisan.distance_km,
            distanceFromClient !== null ? Number(distanceFromClient.toFixed(2)) : null
        ),
        causal_score: firstDefined(artisan.causal_score, null),
        recommended_because: firstDefined(artisan.recommended_because, ""),
        photo: firstDefined(artisan.photo, null),
    };
}

function sortArtisansByScoreDesc(a, b) {
    const scoreA = Number(a.causal_score ?? 0);
    const scoreB = Number(b.causal_score ?? 0);

    if (Number.isNaN(scoreA) && Number.isNaN(scoreB)) {
        return 0;
    }
    if (Number.isNaN(scoreA)) {
        return 1;
    }
    if (Number.isNaN(scoreB)) {
        return -1;
    }

    return scoreB - scoreA;
}

function getLocalDateValue(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return year + "-" + month + "-" + day;
}

function getCurrentDateTimeKey() {
    const now = new Date();

    return getLocalDateValue(now) + "T" + String(now.getHours()).padStart(2, "0") + ":" + String(now.getMinutes()).padStart(2, "0");
}

function formatReservationDate(value) {
    if (!value) {
        return "Date non renseignee";
    }

    const parsed = new Date(value + "T00:00:00");

    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return parsed.toLocaleDateString("fr-FR", {
        weekday: "short",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}

function formatReservationDateTime(dateValue, timeValue) {
    return formatReservationDate(dateValue) + (timeValue ? " a " + timeValue : "");
}

function formatAverageRating(value) {
    const numericValue = Number(value);

    if (!Number.isFinite(numericValue) || numericValue <= 0) {
        return "-";
    }

    return numericValue.toFixed(1);
}

function normalizeReservation(reservation) {
    return {
        id: String(firstDefined(reservation?.id, "")),
        clientName: firstDefined(reservation?.clientName, reservation?.client_name, currentUser),
        artisanName: firstDefined(reservation?.artisanName, reservation?.artisan_name, "Artisan"),
        artisanId: firstDefined(reservation?.artisanId, reservation?.artisan_id, null),
        serviceType: firstDefined(reservation?.serviceType, reservation?.service_type, "Service non renseigne"),
        city: firstDefined(reservation?.city, ""),
        price: firstDefined(reservation?.price, reservation?.quoted_price, "-"),
        reservationDate: firstDefined(reservation?.reservationDate, reservation?.reservation_date, getLocalDateValue(new Date())),
        reservationTime: firstDefined(reservation?.reservationTime, reservation?.reservation_time, "10:00"),
        notes: String(firstDefined(reservation?.notes, "")),
        status: firstDefined(reservation?.status, "en_attente"),
        createdAt: firstDefined(reservation?.createdAt, reservation?.created_at, new Date().toISOString()),
    };
}

function getReservationStatusMeta(status) {
    if (status === "confirmee") {
        return {
            label: "Confirmee",
            classes: "bg-emerald-100 text-emerald-800",
        };
    }

    if (status === "annulee") {
        return {
            label: "Annulee",
            classes: "bg-rose-100 text-rose-700",
        };
    }

    return {
        label: "En attente",
        classes: "bg-amber-100 text-amber-800",
    };
}

function sortReservations(reservations) {
    return reservations.slice().sort(function (left, right) {
        const leftKey = left.reservationDate + "T" + left.reservationTime;
        const rightKey = right.reservationDate + "T" + right.reservationTime;
        return leftKey.localeCompare(rightKey);
    });
}

function uniqueReservations(reservations) {
    const seen = new Set();

    return reservations.filter(function (reservation) {
        const key = reservation.id
            ? "id:" + reservation.id
            : [
                reservation.artisanId,
                reservation.reservationDate,
                reservation.reservationTime,
                reservation.notes,
            ].join("|");

        if (seen.has(key)) {
            return false;
        }

        seen.add(key);
        return true;
    });
}

function hasReservationForSelectedArtisanDate(dateKey) {
    if (!state.selectedArtisan || !dateKey) {
        return false;
    }

    return state.reservations.some(function (reservation) {
        return String(reservation.artisanId) === String(state.selectedArtisan.artisan_id)
            && reservation.reservationDate === dateKey;
    });
}

function getSelectedArtisanAvailabilityKind(dateKey) {
    if (!dateKey) {
        return "unset";
    }

    if (dateKey < getLocalDateValue(new Date())) {
        return "past";
    }

    const value = state.selectedArtisanAvailability[dateKey];

    if (value === false) {
        return "unavailable";
    }

    return "available";
}

function getSelectedArtisanAvailabilityMeta(dateKey) {
    const kind = getSelectedArtisanAvailabilityKind(dateKey);

    if (kind === "available") {
        return {
            kind: kind,
            label: "Disponible",
            classes: "bg-emerald-100 text-emerald-800",
            description: "Ce jour est ouvert a la reservation chez cet artisan.",
        };
    }

    if (kind === "unavailable") {
        return {
            kind: kind,
            label: "Indisponible",
            classes: "bg-rose-100 text-rose-700",
            description: "Cet artisan a marque cette date comme indisponible.",
        };
    }

    if (kind === "past") {
        return {
            kind: kind,
            label: "Jour passe",
            classes: "bg-slate-200 text-slate-600",
            description: "Les jours passes ne sont plus reservables.",
        };
    }

    return {
        kind: kind,
        label: "Disponible",
        classes: "bg-emerald-100 text-emerald-800",
        description: "Ce jour est disponible par defaut chez cet artisan.",
    };
}

function getNextAvailableReservationDate(startDateKey) {
    const cursor = new Date((startDateKey || getLocalDateValue(new Date())) + "T00:00:00");

    for (let index = 0; index < 365; index += 1) {
        const dateKey = getLocalDateValue(cursor);
        if (getSelectedArtisanAvailabilityKind(dateKey) === "available") {
            return dateKey;
        }
        cursor.setDate(cursor.getDate() + 1);
    }

    return getLocalDateValue(new Date());
}

function buildCalendarDays(cursorDate, reservations, selectedDate) {
    const year = cursorDate.getFullYear();
    const month = cursorDate.getMonth();
    const firstDay = new Date(year, month, 1);
    const firstWeekDay = (firstDay.getDay() + 6) % 7;
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const previousMonthDays = new Date(year, month, 0).getDate();
    const days = [];

    for (let index = firstWeekDay - 1; index >= 0; index -= 1) {
        const dayNumber = previousMonthDays - index;
        const date = new Date(year, month - 1, dayNumber);
        days.push({
            key: getLocalDateValue(date),
            label: dayNumber,
            outsideMonth: true,
            count: reservations.filter(function (reservation) {
                return reservation.reservationDate === getLocalDateValue(date);
            }).length,
        });
    }

    for (let dayNumber = 1; dayNumber <= daysInMonth; dayNumber += 1) {
        const date = new Date(year, month, dayNumber);
        days.push({
            key: getLocalDateValue(date),
            label: dayNumber,
            outsideMonth: false,
            count: reservations.filter(function (reservation) {
                return reservation.reservationDate === getLocalDateValue(date);
            }).length,
        });
    }

    while (days.length % 7 !== 0 || days.length < 35) {
        const nextIndex = days.length - (firstWeekDay + daysInMonth) + 1;
        const date = new Date(year, month + 1, nextIndex);
        days.push({
            key: getLocalDateValue(date),
            label: nextIndex,
            outsideMonth: true,
            count: reservations.filter(function (reservation) {
                return reservation.reservationDate === getLocalDateValue(date);
            }).length,
        });
    }

    return days.map(function (day) {
        const availabilityMeta = getSelectedArtisanAvailabilityMeta(day.key);

        return {
            ...day,
            isToday: day.key === getLocalDateValue(new Date()),
            isSelected: day.key === selectedDate,
            availabilityKind: availabilityMeta.kind,
            availabilityLabel: availabilityMeta.label,
        };
    });
}

async function syncSelectedArtisanAvailability(force) {
    const artisanId = state.selectedArtisan ? String(state.selectedArtisan.artisan_id) : null;

    if (!artisanId) {
        state.selectedArtisanAvailability = {};
        state.selectedArtisanAvailabilityFor = null;
        state.availabilityLoading = false;
        return;
    }

    if (!force && state.selectedArtisanAvailabilityFor === artisanId) {
        return;
    }

    state.availabilityLoading = true;
    state.selectedArtisanAvailability = {};
    state.selectedArtisanAvailabilityFor = artisanId;
    renderReservations();

    try {
        const payload = await fetchJson(
            `${artisanAvailabilityBaseUrl}/${encodeURIComponent(artisanId)}/availability`,
            {
                method: "GET",
            },
            "Impossible de charger les disponibilites de l'artisan."
        );

        state.selectedArtisanAvailability = (Array.isArray(payload) ? payload : []).reduce(function (carry, entry) {
            if (entry && entry.availableDate) {
                carry[entry.availableDate] = Boolean(entry.isAvailable);
            }

            return carry;
        }, {});

        if (getSelectedArtisanAvailabilityKind(state.reservationDate) !== "available") {
            state.reservationDate = getNextAvailableReservationDate(getLocalDateValue(new Date()));
            state.calendarCursor = new Date(state.reservationDate + "T00:00:00");
            state.calendarCursor = new Date(state.calendarCursor.getFullYear(), state.calendarCursor.getMonth(), 1);
        }
    } catch (error) {
        state.error = error.message || "Impossible de charger les disponibilites de l'artisan.";
        state.success = "";
        renderAlerts();
    } finally {
        state.availabilityLoading = false;
        renderReservations();
        renderHeaderStats();
    }
}

function openReservationComposer(artisan, reservationDate) {
    if (artisan) {
        state.selectedArtisan = artisan;
        state.reservationNotes = "Intervention souhaitee pour " + artisan.service_type + ".";
    }

    if (reservationDate) {
        state.reservationDate = reservationDate;
        state.calendarCursor = new Date(reservationDate + "T00:00:00");
        state.calendarCursor = new Date(state.calendarCursor.getFullYear(), state.calendarCursor.getMonth(), 1);
    }

    closeArtisanProfile();
    setActiveSection("reservations");
    renderAll();
}

function syncArtisanRatingAcrossState(artisanId, averageRating) {
    const formattedRating = formatAverageRating(averageRating);

    function applyRating(artisan) {
        if (!artisan || String(artisan.artisan_id) !== String(artisanId)) {
            return artisan;
        }

        return {
            ...artisan,
            rating: formattedRating,
        };
    }

    state.artisans = state.artisans.map(applyRating);
    state.recommendedArtisans = state.recommendedArtisans.map(applyRating);
    state.complementaryServices = state.complementaryServices.map(applyRating);
    state.selectedArtisan = applyRating(state.selectedArtisan);
    state.profilePreviewArtisan = applyRating(state.profilePreviewArtisan);
}

function setActiveSection(sectionId) {
    if (sectionId === "reservations" && !state.selectedArtisan) {
        return;
    }

    state.activeSection = sectionId;
    const shouldShowSearchLayout = sectionId === "search" || sectionId === "map";
    dom.sectionSearch.classList.toggle("hidden", !shouldShowSearchLayout);
    dom.sectionMap.classList.add("hidden");
    dom.sectionProfiles.classList.toggle("hidden", sectionId !== "profiles");
    dom.sectionMessaging.classList.toggle("hidden", sectionId !== "messaging");
    dom.sectionChat.classList.toggle("hidden", sectionId !== "chat");
    dom.sectionReservations.classList.toggle("hidden", sectionId !== "reservations");

    renderSectionNavigation();

    if (shouldShowSearchLayout && map) {
        window.setTimeout(function () {
            map.invalidateSize();

            if (sectionId === "map" && dom.searchMapCard) {
                dom.searchMapCard.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        }, 120);
    }
}

function renderSectionNavigation() {
    dom.sectionButtons.forEach(function (button) {
        const sectionId = state.activeSection;
        const isActive = button.dataset.sectionButton === sectionId;
        const isDisabled = button.dataset.sectionButton === "reservations" && !state.selectedArtisan;
        button.disabled = isDisabled;
        button.className = isDisabled
            ? "cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-left text-slate-400 opacity-70"
            : (isActive
                ? "rounded-2xl bg-slate-900 px-4 py-3 text-left text-white shadow-[0_16px_35px_rgba(15,23,42,0.18)] transition"
                : "rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-slate-700 transition hover:border-teal-300 hover:text-teal-700");
        button.querySelector("p:last-child").className = isDisabled
            ? "mt-1 text-xs text-slate-400"
            : (isActive ? "mt-1 text-xs text-slate-200" : "mt-1 text-xs text-slate-500");
    });
}

function showPreparedRequests() {
    setActiveSection("search");
    state.success = state.requestsCount > 0
        ? "Vous avez " + state.requestsCount + " demande(s) prête(s)."
        : "Aucune demande préparée pour le moment.";
    state.error = null;
    renderAlerts();
}

function showSearchHistory() {
    if (dom.profilesSearchHistoryBlock && dom.profilesSearchHistoryList) {
        renderSearchHistory();
    }
    state.success = state.profilesSearchHistory.length > 0
        ? "Voici votre historique des recherches. Cliquez sur un terme pour relancer la recherche."
        : "Aucun historique pour le moment. Faites une recherche pour en enregistrer.";
    state.error = null;
    renderAlerts();
}

function renderAlerts() {
    if (!state.error && !state.success) {
        dom.alerts.classList.add("hidden");
        dom.alerts.innerHTML = "";
        return;
    }

    dom.alerts.classList.remove("hidden");
    dom.alerts.innerHTML = "";

    if (state.error) {
        const errorBlock = document.createElement("div");
        errorBlock.className = "rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700";
        errorBlock.textContent = state.error;
        dom.alerts.appendChild(errorBlock);
    }

    if (state.success) {
        const successBlock = document.createElement("div");
        successBlock.className = "rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700";
        successBlock.textContent = state.success;
        dom.alerts.appendChild(successBlock);
    }
}

function renderNotifications() {
    if (!dom.notificationsList || !dom.notificationsCountBadge) {
        return;
    }

    dom.notificationsCountBadge.textContent = String(state.unreadNotificationsCount);
    dom.notificationsCountBadge.className = state.unreadNotificationsCount > 0
        ? "rounded-full bg-rose-600 px-2 py-0.5 text-xs font-extrabold text-white"
        : "rounded-full bg-slate-300 px-2 py-0.5 text-xs font-extrabold text-slate-700";

    if (state.notifications.length === 0) {
        dom.notificationsList.innerHTML = `
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm font-semibold text-slate-500">
                Aucune notification.
            </div>
        `;
        return;
    }

    dom.notificationsList.innerHTML = state.notifications.map(function (notification) {
        const data = notification.data || {};
        const unreadClass = notification.readAt ? "border-slate-200 bg-white" : "border-teal-200 bg-teal-50";

        return `
            <article class="rounded-2xl border ${unreadClass} p-3">
                <p class="text-sm font-extrabold text-slate-900">${escapeHtml(data.title || "Notification")}</p>
                <p class="mt-1 text-xs leading-5 text-slate-600">${escapeHtml(data.message || "")}</p>
            </article>
        `;
    }).join("");
}

function renderHeaderStats() {
    renderSectionNavigation();
    dom.requestsCountBadge.textContent = state.requestsCount + " demande" + (state.requestsCount > 1 ? "s" : "") + " preparee" + (state.requestsCount > 1 ? "s" : "");
    dom.reservationsCountBadge.textContent = state.reservations.length + " reservation" + (state.reservations.length > 1 ? "s" : "");
    dom.heroResultsCount.textContent = String(state.artisans.length);
    dom.heroSelectedCount.textContent = state.selectedArtisan ? "1" : "0";
    dom.heroPositionCount.textContent = state.position ? "ok" : (state.geoPending ? "..." : "non");
    if (dom.clientMenuReservationsCount) {
        dom.clientMenuReservationsCount.textContent = String(state.reservations.length);
    }
    if (dom.clientMenuReservations) {
        dom.clientMenuReservations.disabled = !state.selectedArtisan;
        dom.clientMenuReservations.className = state.selectedArtisan
            ? "mb-3 flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-800 transition hover:border-slate-300 hover:bg-slate-100"
            : "mb-3 flex w-full cursor-not-allowed items-center justify-between rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-400 opacity-70";
    }
    if (dom.clientMenuRequestsCount) {
        dom.clientMenuRequestsCount.textContent = String(state.requestsCount);
    }
    dom.positionLabel.textContent = state.position
        ? state.position.lat.toFixed(4) + ", " + state.position.lng.toFixed(4)
        : (state.geoPending ? "Localisation en cours..." : "En attente de geolocalisation");
    dom.actionLabel.textContent = state.searchSummary;
    dom.resultsTitle.textContent = state.resultsTitle;
    dom.profilesCountBadge.textContent = state.artisans.length + " profil" + (state.artisans.length > 1 ? "s" : "");
    dom.mapPointsBadge.textContent = state.artisans.length + " point" + (state.artisans.length > 1 ? "s" : "");
    dom.chatCountBadge.textContent = state.acceptedRequests.length + " active" + (state.acceptedRequests.length > 1 ? "s" : "");
    dom.reservationsListBadge.textContent = state.reservations.length + " active" + (state.reservations.length > 1 ? "s" : "");

    const nextReservation = state.reservations.find(function (reservation) {
        return reservation.reservationDate + "T" + reservation.reservationTime >= getCurrentDateTimeKey();
    }) || state.reservations[0] || null;
    dom.reservationNextBadge.textContent = nextReservation
        ? formatReservationDateTime(nextReservation.reservationDate, nextReservation.reservationTime)
        : "Aucun rendez-vous";
    dom.calendarTitle.textContent = state.calendarCursor.toLocaleDateString("fr-FR", {
        month: "long",
        year: "numeric",
    });

    if (state.position) {
        dom.geoIndicator.className = "inline-flex h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_0_6px_rgba(34,197,94,0.14)]";
        dom.geoStatus.textContent = "Geolocalisation active";
    } else if (state.geoPending) {
        dom.geoIndicator.className = "inline-flex h-3 w-3 rounded-full bg-sky-500 shadow-[0_0_0_6px_rgba(14,165,233,0.14)]";
        dom.geoStatus.textContent = "Geolocalisation en cours...";
    } else {
        dom.geoIndicator.className = "inline-flex h-3 w-3 rounded-full bg-slate-300";
        dom.geoStatus.textContent = "Geolocalisation en attente";
    }


}

function renderProfiles() {
    renderSearchHistory();
    renderFavoriteArtisans();

    if (state.profilesSearchLoading) {
        dom.profilesResults.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <h4 class="text-lg font-extrabold text-slate-900">Recherche en cours</h4>
                <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-500">
                    Nous cherchons les artisans qui correspondent aux champs saisis.
                </p>
            </div>
        `;
        dom.profilesCountBadge.textContent = "...";
        renderComplementaryServices();
        return;
    }

    if (state.artisans.length === 0) {
        dom.profilesResults.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <h4 class="text-lg font-extrabold text-slate-900">${state.profilesSearchQuery.trim() || state.profilesSearchService.trim() || state.profilesSearchCommune ? "Aucun artisan trouve" : "Aucun resultat pour le moment"}</h4>
                <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-500">
                    ${state.profilesSearchQuery.trim() || state.profilesSearchService.trim() || state.profilesSearchCommune
                        ? "Aucun artisan ne correspond aux champs saisis."
                        : "Lancez une recherche par service pour afficher des artisans et commencer une prise de contact."}
                </p>
            </div>
        `;
        renderComplementaryServices();
        return;
    }

    const searchQuery = state.profilesSearchQuery.trim().toLowerCase();
    const filteredArtisans = state.artisans
        .map(function (artisan, index) {
            return { artisan: artisan, originalIndex: index };
        })
        .filter(function (item) {
            return !searchQuery || item.artisan.artisan_name.toLowerCase().includes(searchQuery);
        });

    dom.profilesCountBadge.textContent = filteredArtisans.length + " profil" + (filteredArtisans.length > 1 ? "s" : "");

    if (filteredArtisans.length === 0) {
        dom.profilesResults.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <h4 class="text-lg font-extrabold text-slate-900">Aucun artisan trouve</h4>
                <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-500">
                    Aucun artisan avec ce nom n'a été trouvé dans les résultats actuels.
                </p>
            </div>
        `;
        renderComplementaryServices();
        return;
    }

    dom.profilesResults.innerHTML = filteredArtisans.map(function (item, index) {
        const artisan = item.artisan;
        const originalIndex = item.originalIndex;
        const isSelected = state.selectedArtisan && state.selectedArtisan.artisan_id === artisan.artisan_id;
        const isFavorite = isFavoriteArtisan(artisan);
        const rankingLabel = state.profilesSearchQuery.trim() ? "Resultat " + (index + 1) : "Top " + (index + 1);

        return `
            <article class="rounded-[24px] border p-5 transition ${isSelected ? "border-teal-300 bg-teal-50" : "border-slate-200 bg-white hover:border-slate-300"}">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <span class="rounded-full bg-slate-900 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] text-white">${rankingLabel}</span>
                    <button type="button" data-favorite-toggle="${originalIndex}" class="rounded-full border px-4 py-2 text-xs font-extrabold transition ${isFavorite ? "border-amber-300 bg-amber-100 text-amber-800 hover:bg-amber-200" : "border-slate-200 bg-white text-slate-600 hover:border-amber-300 hover:text-amber-700"}">
                        ${isFavorite ? "Favori" : "Ajouter favori"}
                    </button>
                </div>

                <h4 class="text-xl font-extrabold text-slate-900">${artisan.artisan_name}</h4>
                <p class="mt-1 text-sm font-semibold text-slate-500">${artisan.service_type}</p>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Note</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.rating}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Prix</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.price} DZD</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Distance</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.distance_km !== null ? artisan.distance_km + " km" : "-"}</p>
                    </div>
                </div>

                <div class="mt-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Actions rapides</p>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button" data-profile-action="profile" data-original-index="${originalIndex}" class="rounded-full bg-amber-400 px-5 py-2.5 text-sm font-extrabold text-slate-950 shadow-[0_12px_25px_rgba(251,191,36,0.28)] transition hover:bg-amber-300">
                        Voir le profil artisan
                    </button>
                    <button type="button" data-profile-action="contact" data-original-index="${originalIndex}" class="rounded-full bg-violet-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-800">
                        Envoyer demande
                    </button>
                    <button type="button" data-profile-action="reserve" data-original-index="${originalIndex}" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700">
                        Reserver
                    </button>
                    <button type="button" data-profile-action="map" data-original-index="${originalIndex}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">
                        Voir sur la carte
                    </button>
                </div>

                <div class="mt-4 hidden" data-message-form="${originalIndex}">
                    <textarea data-message-text="${originalIndex}" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="Ecrivez votre message...">Bonjour ${artisan.artisan_name}, je suis interesse par votre service de ${artisan.service_type}. Pouvez-vous me contacter ?</textarea>
                    <div class="mt-3 flex gap-3">
                        <button type="button" data-send-request="${originalIndex}" class="rounded-full bg-violet-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-800">
                            Envoyer
                        </button>
                        <button type="button" data-cancel-message="${originalIndex}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-300">
                            Annuler
                        </button>
                    </div>
                </div>
            </article>
        `;
    }).join("");

    dom.profilesResults.querySelectorAll("[data-profile-action]").forEach(function (button) {
        button.addEventListener("click", function () {
            const artisan = state.artisans[Number(button.dataset.originalIndex)];
            if (!artisan) {
                return;
            }

            if (button.dataset.profileAction === "contact") {
                // Show message form
                const form = dom.profilesResults.querySelector(`[data-message-form="${button.dataset.originalIndex}"]`);
                if (form) {
                    form.classList.remove("hidden");
                }
            } else if (button.dataset.profileAction === "reserve") {
                openReservationComposer(artisan, state.reservationDate || getLocalDateValue(new Date()));
            } else if (button.dataset.profileAction === "profile") {
                openArtisanProfile(artisan);
            } else if (button.dataset.profileAction === "map") {
                setActiveSection("map");
                focusArtisanOnMap(artisan);
            }
        });
    });

    dom.profilesResults.querySelectorAll("[data-favorite-toggle]").forEach(function (button) {
        button.addEventListener("click", function () {
            const artisan = state.artisans[Number(button.dataset.favoriteToggle)];
            if (!artisan) {
                return;
            }

            toggleFavoriteArtisan(artisan);
        });
    });

    // Add event listeners for message forms
    dom.profilesResults.querySelectorAll("[data-send-request]").forEach(function (button) {
        button.addEventListener("click", function () {
            const index = button.dataset.sendRequest;
            const artisan = state.artisans[Number(index)];
            const messageText = dom.profilesResults.querySelector(`[data-message-text="${index}"]`).value;
            sendRequestToArtisan(artisan, messageText);
        });
    });

    dom.profilesResults.querySelectorAll("[data-cancel-message]").forEach(function (button) {
        button.addEventListener("click", function () {
            const form = dom.profilesResults.querySelector(`[data-message-form="${button.dataset.cancelMessage}"]`);
            if (form) {
                form.classList.add("hidden");
            }
        });
    });

    renderComplementaryServices();
}

function renderComplementaryServices() {
    if (!dom.complementaryServicesBlock) {
        return;
    }

    if (state.profilesSearchQuery.trim() || state.profilesSearchLoading) {
        dom.complementaryServicesBlock.innerHTML = "";
        return;
    }

    if (state.complementaryServices.length === 0) {
        dom.complementaryServicesBlock.innerHTML = "";
        return;
    }

    const content = state.showComplementaryServices
        ? `
            <div class="mt-5 space-y-4">
                ${state.complementaryServices.map(function (artisan, index) {
                    const isFavorite = isFavoriteArtisan(artisan);
                    return `
                        <article class="rounded-[24px] border border-emerald-200 bg-emerald-50/60 p-5">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <span class="rounded-full bg-emerald-700 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] text-white">
                                    Service complementaire ${index + 1}
                                </span>
                                <div class="flex flex-wrap justify-end gap-2">
                                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] text-emerald-700">
                                        ${artisan.service_type}
                                    </span>
                                    <button type="button" data-complementary-favorite="${index}" class="rounded-full border px-3 py-1.5 text-xs font-extrabold transition ${isFavorite ? "border-amber-300 bg-amber-100 text-amber-800 hover:bg-amber-200" : "border-emerald-200 bg-white text-slate-600 hover:border-amber-300 hover:text-amber-700"}">
                                        ${isFavorite ? "Favori" : "Favori +"}
                                    </button>
                                </div>
                            </div>

                            <h4 class="text-xl font-extrabold text-slate-900">${artisan.artisan_name}</h4>
                            <p class="mt-1 text-sm font-semibold text-slate-600">${artisan.recommended_because || "Suggestion complementaire"}</p>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Note</p>
                                    <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.rating}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Prix</p>
                                    <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.price} DZD</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Distance</p>
                                    <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.distance_km !== null ? artisan.distance_km + " km" : "-"}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-3">
                                <button type="button" data-complementary-action="profile" data-complementary-index="${index}" class="rounded-full bg-amber-400 px-5 py-2.5 text-sm font-extrabold text-slate-950 transition hover:bg-amber-300">
                                    Voir le profil
                                </button>
                                <button type="button" data-complementary-action="contact" data-complementary-index="${index}" class="rounded-full bg-violet-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-800">
                                    Contacter
                                </button>
                                <button type="button" data-complementary-action="map" data-complementary-index="${index}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">
                                    Voir sur la carte
                                </button>
                            </div>
                        </article>
                    `;
                }).join("")}
            </div>
        `
        : "";

    dom.complementaryServicesBlock.innerHTML = `
        <div class="rounded-[24px] border border-emerald-100 bg-[linear-gradient(135deg,#f3fbf6_0%,#ecfdf5_100%)] p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-emerald-700">Question</p>
                    <h4 class="mt-1 text-xl font-extrabold text-slate-900">Voulez-vous voir aussi les services complementaires ?</h4>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Ces suggestions viennent apres vos Top 10 pour completer votre besoin.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" data-toggle-complementary="yes" class="${state.showComplementaryServices ? "bg-emerald-700 text-white" : "bg-white text-emerald-700 border border-emerald-200"} rounded-full px-5 py-2.5 text-sm font-extrabold transition hover:bg-emerald-700 hover:text-white">
                        Oui
                    </button>
                    <button type="button" data-toggle-complementary="no" class="${!state.showComplementaryServices ? "bg-slate-900 text-white" : "bg-white text-slate-700 border border-slate-200"} rounded-full px-5 py-2.5 text-sm font-extrabold transition hover:bg-slate-900 hover:text-white">
                        Non
                    </button>
                </div>
            </div>
            ${content}
        </div>
    `;

    dom.complementaryServicesBlock.querySelectorAll("[data-toggle-complementary]").forEach(function (button) {
        button.addEventListener("click", function () {
            state.showComplementaryServices = button.dataset.toggleComplementary === "yes";
            renderComplementaryServices();
        });
    });

    dom.complementaryServicesBlock.querySelectorAll("[data-complementary-action]").forEach(function (button) {
        button.addEventListener("click", function () {
            const artisan = state.complementaryServices[Number(button.dataset.complementaryIndex)];
            if (!artisan) {
                return;
            }

            if (button.dataset.complementaryAction === "profile") {
                openArtisanProfile(artisan);
            } else if (button.dataset.complementaryAction === "contact") {
                selectArtisan(artisan);
            } else if (button.dataset.complementaryAction === "map") {
                setActiveSection("map");
                focusArtisanOnMap(artisan);
            }
        });
    });

    dom.complementaryServicesBlock.querySelectorAll("[data-complementary-favorite]").forEach(function (button) {
        button.addEventListener("click", function () {
            const artisan = state.complementaryServices[Number(button.dataset.complementaryFavorite)];
            if (!artisan) {
                return;
            }

            toggleFavoriteArtisan(artisan);
        });
    });
}

function renderMessaging() {
    if (!state.selectedArtisan) {
        dom.messagingContent.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <p class="text-sm font-semibold text-slate-500">Selectionnez d'abord un artisan depuis la section profils pour ouvrir la messagerie.</p>
            </div>
        `;
        return;
    }

    dom.messagingContent.innerHTML = `
        <div class="space-y-4">
            <div class="rounded-3xl bg-slate-50 p-4">
                <p class="text-sm font-bold text-slate-900">${state.selectedArtisan.artisan_name}</p>
                <p class="mt-1 text-sm text-slate-500">
                    ${state.selectedArtisan.service_type}${state.selectedArtisan.distance_km !== null ? " • " + state.selectedArtisan.distance_km + " km" : ""}
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button id="messaging-open-profile" type="button" class="rounded-full bg-amber-400 px-5 py-2.5 text-sm font-extrabold text-slate-950 shadow-[0_12px_25px_rgba(251,191,36,0.28)] transition hover:bg-amber-300">
                        Voir le profil artisan
                    </button>
                    <button id="messaging-back-profiles" type="button" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-violet-300 hover:text-violet-700">
                        Retour aux profils
                    </button>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Le profil complet de cet artisan est accessible directement depuis ce bouton.
                </p>
            </div>

            <textarea id="messageText" rows="6" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="Ecrivez votre message...">${state.messageText}</textarea>

            <button id="send-request-button" type="button" class="w-full rounded-2xl bg-violet-700 px-5 py-4 text-sm font-extrabold text-white transition hover:bg-violet-800">
                Envoyer la demande a l'artisan
            </button>
        </div>
    `;

    dom.messagingContent.querySelector("#messaging-open-profile").addEventListener("click", function () {
        openArtisanProfile(state.selectedArtisan);
    });

    dom.messagingContent.querySelector("#messaging-back-profiles").addEventListener("click", function () {
        setActiveSection("profiles");
    });

    dom.messagingContent.querySelector("#messageText").addEventListener("input", function (event) {
        state.messageText = event.target.value;
    });

    dom.messagingContent.querySelector("#send-request-button").addEventListener("click", submitRequest);
}

function renderChat() {
    if (state.acceptedRequests.length === 0) {
        dom.chatContent.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <p class="text-sm font-semibold text-slate-500">Le chat sera disponible apres acceptation de votre demande par l'artisan.</p>
            </div>
        `;
        return;
    }

    const activeAcceptedRequest = state.acceptedRequests.find(function (request) {
        return request.chatId === state.activeChatId;
    }) || null;

    dom.chatContent.innerHTML = `
        <div class="space-y-4">
            <div class="flex flex-wrap gap-2 rounded-[26px] border border-white/70 bg-white/85 p-3 shadow-[0_16px_40px_rgba(15,23,42,0.08)]">
                ${state.acceptedRequests.map(function (request) {
                    const isActive = state.activeChatId === request.chatId;
                    const classes = isActive
                        ? "rounded-full bg-[linear-gradient(135deg,#0f172a_0%,#334155_100%)] px-4 py-2 text-sm font-bold text-white shadow-[0_10px_24px_rgba(15,23,42,0.22)] transition"
                        : "rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700";
                    return '<button type="button" data-chat-switch="' + request.chatId + '" class="' + classes + '">' + request.artisanName + '</button>';
                }).join("")}
            </div>

            <div class="overflow-hidden rounded-[30px] border border-white/70 bg-white/90 shadow-[0_24px_70px_rgba(15,23,42,0.10)] backdrop-blur">
                <div class="border-b border-slate-100 bg-[linear-gradient(135deg,#ecfeff_0%,#f8fafc_100%)] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f766e_0%,#14b8a6_100%)] text-sm font-extrabold text-white shadow-[0_12px_25px_rgba(13,148,136,0.28)]">
                            ${escapeHtml((activeAcceptedRequest ? activeAcceptedRequest.artisanName : "C").trim().charAt(0).toUpperCase())}
                        </div>
                        <div>
                            <p class="text-sm font-extrabold text-slate-900">${activeAcceptedRequest ? activeAcceptedRequest.artisanName : "Conversation"}</p>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Discussion active</p>
                        </div>
                    </div>
                </div>
                <div class="max-h-[28rem] space-y-4 overflow-y-auto bg-[radial-gradient(circle_at_top,#f8fafc_0%,#eef6f6_55%,#f8fafc_100%)] px-4 py-5 sm:px-5">
                    ${state.chatMessages.map(function (message) {
                        return renderChatMessage(message, "client");
                    }).join("")}
                </div>

                <div class="border-t border-slate-100 bg-white px-4 py-4 sm:px-5">
                    <div class="space-y-3 rounded-[28px] border border-slate-200 bg-slate-50/80 p-3 shadow-inner">
                    <div class="flex flex-wrap gap-2">
                        <input id="chat-image-input" type="file" accept="image/*" class="hidden">
                        <button id="chat-add-photo" type="button" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-700">
                            Photo
                        </button>
                        <button id="chat-record-voice" type="button" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold transition ${state.chatRecording ? "border-rose-300 bg-rose-50 text-rose-600" : "text-slate-700 hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-700"}">
                            ${state.chatRecording ? "Arreter le vocal" : "Vocal"}
                        </button>
                        ${(state.pendingChatImageDataUrl || state.pendingChatVoiceDataUrl) ? `
                            <button id="chat-clear-attachments" type="button" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600">
                                Effacer les pieces jointes
                            </button>
                        ` : ""}
                    </div>

                    ${state.chatRecording ? `
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                            Enregistrement vocal en cours... cliquez sur "Arreter le vocal" pour finaliser.
                        </div>
                    ` : ""}

                    ${(state.pendingChatImageDataUrl || state.pendingChatVoiceDataUrl) ? `
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">Pieces jointes en attente</p>
                            ${state.pendingChatImageDataUrl ? `<img src="${state.pendingChatImageDataUrl}" alt="${escapeHtml(state.pendingChatImageName || "Photo")}" class="mt-3 max-h-56 w-full rounded-2xl object-cover">` : ""}
                            ${state.pendingChatVoiceDataUrl ? `
                                <div class="mt-3">
                                    <audio controls class="w-full max-w-sm">
                                        <source src="${state.pendingChatVoiceDataUrl}" type="${escapeHtml(state.pendingChatVoiceMimeType || "audio/webm")}">
                                        Votre navigateur ne supporte pas l'audio.
                                    </audio>
                                </div>
                            ` : ""}
                        </div>
                    ` : ""}

                    <div class="flex gap-3">
                        <input id="chat-input" type="text" value="" placeholder="Aa" class="flex-1 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        <button id="send-chat-button" type="button" class="rounded-full bg-[linear-gradient(135deg,#4338ca_0%,#6366f1_100%)] px-5 py-3 text-sm font-extrabold text-white shadow-[0_14px_30px_rgba(79,70,229,0.28)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_36px_rgba(79,70,229,0.32)]">
                            Envoyer
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    dom.chatContent.querySelectorAll("[data-chat-switch]").forEach(function (button) {
        button.addEventListener("click", function () {
            state.activeChatId = button.dataset.chatSwitch;
            syncClientData();
            renderAll();
        });
    });

    const chatInput = dom.chatContent.querySelector("#chat-input");
    const sendButton = dom.chatContent.querySelector("#send-chat-button");
    const imageInput = dom.chatContent.querySelector("#chat-image-input");
    const photoButton = dom.chatContent.querySelector("#chat-add-photo");
    const voiceButton = dom.chatContent.querySelector("#chat-record-voice");
    const clearButton = dom.chatContent.querySelector("#chat-clear-attachments");

    sendButton.addEventListener("click", function () {
        sendChatMessage(chatInput ? chatInput.value : "");
    });

    chatInput.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendChatMessage(chatInput.value);
        }
    });

    if (photoButton && imageInput) {
        photoButton.addEventListener("click", function () {
            imageInput.click();
        });

        imageInput.addEventListener("change", onClientChatImageSelected);
    }

    if (voiceButton) {
        voiceButton.addEventListener("click", toggleClientVoiceRecording);
    }

    if (clearButton) {
        clearButton.addEventListener("click", function () {
            clearPendingChatAttachments();
            state.error = "";
            state.success = "";
            renderChat();
            renderAlerts();
        });
    }
}

function renderReservations() {
    const currentArtisanId = state.selectedArtisan ? String(state.selectedArtisan.artisan_id) : null;

    if (!currentArtisanId) {
        state.selectedArtisanAvailability = {};
        state.selectedArtisanAvailabilityFor = null;
        state.availabilityLoading = false;
    } else if (state.selectedArtisanAvailabilityFor !== currentArtisanId && !state.availabilityLoading) {
        void syncSelectedArtisanAvailability(true);
    }

    const selectedDate = state.reservationDate || getLocalDateValue(new Date());
    const reservationsForSelectedDate = state.reservations.filter(function (reservation) {
        return reservation.reservationDate === selectedDate;
    });
    const calendarDays = buildCalendarDays(state.calendarCursor, state.reservations, selectedDate);
    const weekDays = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
    const selectedAvailability = getSelectedArtisanAvailabilityMeta(selectedDate);
    const alreadyReservedForSelectedDay = hasReservationForSelectedArtisanDate(selectedDate);
    const canSubmitReservation = Boolean(state.selectedArtisan)
        && selectedAvailability.kind === "available"
        && !alreadyReservedForSelectedDay;

    dom.reservationsContent.innerHTML = `
        <div class="space-y-5">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Artisan</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${state.selectedArtisan ? state.selectedArtisan.artisan_name : "Aucun artisan choisi"}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Service</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${state.selectedArtisan ? state.selectedArtisan.service_type : "Selection requise"}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Date cible</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${formatReservationDate(selectedDate)}</p>
                </div>
            </div>

            ${state.selectedArtisan ? `
                <div class="rounded-[28px] border border-emerald-100 bg-[linear-gradient(135deg,#f0fdf4_0%,#ecfeff_100%)] p-5">
                    <p class="text-sm font-semibold leading-6 text-slate-600">
                        Vous planifiez une intervention avec <span class="font-extrabold text-slate-900">${escapeHtml(state.selectedArtisan.artisan_name)}</span>.
                        Choisissez un jour vert dans le calendrier puis confirmez le creneau.
                    </p>
                </div>
            ` : `
                <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50 px-5 py-6 text-sm font-semibold leading-7 text-slate-500">
                    Ouvrez d'abord un profil artisan puis cliquez sur "Reserver" pour pre-remplir le formulaire de reservation.
                </div>
            `}

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="reservation-date" class="mb-2 block text-sm font-bold text-slate-800">Date</label>
                    <input id="reservation-date" type="date" min="${getLocalDateValue(new Date())}" value="${selectedDate}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="reservation-time" class="mb-2 block text-sm font-bold text-slate-800">Heure</label>
                    <input id="reservation-time" type="time" value="${state.reservationTime}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>
            </div>

            <div class="rounded-3xl bg-slate-50 p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Etat du jour choisi</p>
                    <span class="rounded-full px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] ${selectedAvailability.classes}">
                        ${selectedAvailability.label}
                    </span>
                </div>
                <p class="mt-3 text-sm font-semibold leading-6 text-slate-600">
                    ${state.availabilityLoading ? "Chargement des disponibilites de l'artisan..." : selectedAvailability.description}
                </p>
            </div>

            <div>
                <label for="reservation-notes" class="mb-2 block text-sm font-bold text-slate-800">Details de la reservation</label>
                <textarea id="reservation-notes" rows="5" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100" placeholder="Precisez votre besoin, l'adresse ou les contraintes du rendez-vous...">${escapeHtml(state.reservationNotes)}</textarea>
            </div>

            <div class="flex flex-wrap gap-3">
                <button id="submit-reservation-button" type="button" ${canSubmitReservation ? "" : "disabled"} class="rounded-full bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300">
                    ${alreadyReservedForSelectedDay ? "Deja reserve pour ce jour" : "Confirmer la reservation"}
                </button>
                <button id="reservation-reset-button" type="button" class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-300">
                    Reinitialiser
                </button>
            </div>
        </div>
    `;

    dom.reservationsCalendar.innerHTML = `
        <div class="space-y-5">
            <div class="grid grid-cols-7 gap-2">
                ${weekDays.map(function (day) {
                    return '<div class="px-1 py-2 text-center text-[11px] font-extrabold uppercase tracking-[0.2em] text-slate-400">' + day + '</div>';
                }).join("")}
                ${calendarDays.map(function (day) {
                    const disabled = day.availabilityKind === "past" || day.availabilityKind === "unavailable";
                    let baseClasses = day.isSelected
                        ? "border-emerald-400 bg-emerald-50 text-emerald-900 shadow-[0_10px_24px_rgba(16,185,129,0.15)]"
                        : (day.outsideMonth ? "border-slate-100 bg-slate-50 text-slate-400" : "border-slate-200 bg-white text-slate-800 hover:border-sky-300 hover:text-sky-700");

                    if (day.availabilityKind === "available") {
                        baseClasses = day.isSelected
                            ? "border-emerald-400 bg-emerald-50 text-emerald-900 shadow-[0_10px_24px_rgba(16,185,129,0.15)]"
                            : "border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-300";
                    } else if (day.availabilityKind === "unavailable") {
                        baseClasses = "border-rose-200 bg-rose-50 text-rose-700 cursor-not-allowed";
                    } else if (day.availabilityKind === "past") {
                        baseClasses = "border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed";
                    }

                    const todayRing = day.isToday ? " ring-2 ring-sky-200" : "";
                    const counter = day.count > 0
                        ? '<span class="mt-2 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-900 px-2 text-[11px] font-extrabold text-white">' + day.count + '</span>'
                        : '<span class="mt-2 inline-flex h-2 w-2 rounded-full bg-slate-200"></span>';

                    return `
                        <button type="button" data-calendar-day="${day.key}" ${disabled ? "disabled" : ""} class="min-h-[92px] rounded-2xl border px-2 py-3 text-left text-sm font-bold transition ${baseClasses}${todayRing}">
                            <span class="block">${day.label}</span>
                            <span class="mt-2 inline-flex rounded-full px-2 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] ${day.availabilityKind === "available" ? "bg-emerald-100 text-emerald-800" : (day.availabilityKind === "unavailable" ? "bg-rose-100 text-rose-700" : "bg-slate-200 text-slate-600")}">
                                ${day.availabilityKind === "available" ? "Dispo" : (day.availabilityKind === "unavailable" ? "Pas dispo" : "Passe")}
                            </span>
                            ${counter}
                        </button>
                    `;
                }).join("")}
            </div>

            <div class="rounded-3xl bg-slate-50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Jour selectionne</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900">${formatReservationDate(selectedDate)}</p>
                    </div>
                    <span class="rounded-full bg-sky-100 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] text-sky-800">
                        ${reservationsForSelectedDate.length} reservation${reservationsForSelectedDate.length > 1 ? "s" : ""}
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    ${reservationsForSelectedDate.length > 0 ? reservationsForSelectedDate.map(function (reservation) {
                        const status = getReservationStatusMeta(reservation.status);

                        return `
                            <article class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">${escapeHtml(reservation.artisanName)}</p>
                                        <p class="mt-1 text-sm text-slate-500">${escapeHtml(reservation.serviceType)} • ${reservation.reservationTime}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-[0.18em] ${status.classes}">${status.label}</span>
                                </div>
                            </article>
                        `;
                    }).join("") : `
                        <p class="text-sm font-semibold leading-6 text-slate-500">
                            Aucun rendez-vous enregistre pour cette date.
                        </p>
                    `}
                </div>
            </div>
        </div>
    `;

    dom.reservationsList.innerHTML = state.reservations.length > 0 ? `
        <div class="space-y-4">
            ${state.reservations.map(function (reservation) {
                const status = getReservationStatusMeta(reservation.status);

                return `
                    <article class="rounded-[24px] border border-slate-200 bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h4 class="text-lg font-extrabold text-slate-900">${escapeHtml(reservation.artisanName)}</h4>
                                    <span class="rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-[0.18em] ${status.classes}">${status.label}</span>
                                </div>
                                <p class="mt-2 text-sm font-semibold text-slate-500">${escapeHtml(reservation.serviceType)} • ${formatReservationDateTime(reservation.reservationDate, reservation.reservationTime)}</p>
                                <p class="mt-3 text-sm leading-6 text-slate-600">${escapeHtml(reservation.notes || "Aucun detail complementaire.")}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" data-reservation-focus="${reservation.reservationDate}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-sky-300 hover:text-sky-700">
                                    Voir dans le calendrier
                                </button>
                                ${reservation.status === "en_attente" ? `
                                    <button type="button" data-reservation-delete="${reservation.id}" class="rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                                        Annuler
                                    </button>
                                ` : `
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-bold text-slate-400">
                                        Annulation impossible
                                    </span>
                                `}
                            </div>
                        </div>
                    </article>
                `;
            }).join("")}
        </div>
    ` : `
        <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
            <p class="text-sm font-semibold text-slate-500">Aucune reservation pour le moment. Lancez une recherche puis choisissez un artisan pour fixer un rendez-vous.</p>
        </div>
    `;

    const reservationDateInput = dom.reservationsContent.querySelector("#reservation-date");
    const reservationTimeInput = dom.reservationsContent.querySelector("#reservation-time");
    const reservationNotesInput = dom.reservationsContent.querySelector("#reservation-notes");
    const submitReservationButton = dom.reservationsContent.querySelector("#submit-reservation-button");
    const reservationResetButton = dom.reservationsContent.querySelector("#reservation-reset-button");

    reservationDateInput.addEventListener("input", function (event) {
        state.reservationDate = event.target.value;
        if (state.reservationDate) {
            state.calendarCursor = new Date(state.reservationDate + "T00:00:00");
            state.calendarCursor = new Date(state.calendarCursor.getFullYear(), state.calendarCursor.getMonth(), 1);
        }
        renderReservations();
        renderHeaderStats();
    });

    reservationTimeInput.addEventListener("input", function (event) {
        state.reservationTime = event.target.value;
    });

    reservationNotesInput.addEventListener("input", function (event) {
        state.reservationNotes = event.target.value;
    });

    submitReservationButton.addEventListener("click", submitReservation);

    reservationResetButton.addEventListener("click", function () {
        const firstAvailableDate = Object.keys(state.selectedArtisanAvailability)
            .filter(function (dateKey) {
                return state.selectedArtisanAvailability[dateKey] === true && dateKey >= getLocalDateValue(new Date());
            })
            .sort()[0];

        state.reservationDate = firstAvailableDate || getLocalDateValue(new Date());
        state.reservationTime = "10:00";
        state.reservationNotes = state.selectedArtisan ? "Intervention souhaitee pour " + state.selectedArtisan.service_type + "." : "";
        renderReservations();
    });

    dom.reservationsCalendar.querySelectorAll("[data-calendar-day]").forEach(function (button) {
        button.addEventListener("click", function () {
            state.reservationDate = button.dataset.calendarDay;
            state.calendarCursor = new Date(state.reservationDate + "T00:00:00");
            state.calendarCursor = new Date(state.calendarCursor.getFullYear(), state.calendarCursor.getMonth(), 1);
            renderReservations();
            renderHeaderStats();
        });
    });

    dom.reservationsList.querySelectorAll("[data-reservation-focus]").forEach(function (button) {
        button.addEventListener("click", function () {
            state.reservationDate = button.dataset.reservationFocus;
            state.calendarCursor = new Date(state.reservationDate + "T00:00:00");
            state.calendarCursor = new Date(state.calendarCursor.getFullYear(), state.calendarCursor.getMonth(), 1);
            renderReservations();
            renderHeaderStats();
        });
    });

    dom.reservationsList.querySelectorAll("[data-reservation-delete]").forEach(function (button) {
        button.addEventListener("click", function () {
            cancelReservation(button.dataset.reservationDelete);
        });
    });
}

function renderRatingStars(value, buttonMode) {
    const selectedValue = Number(value) || 0;

    return Array.from({ length: 5 }, function (_, index) {
        const starValue = index + 1;
        const isActive = starValue <= selectedValue;
        const starColor = isActive ? "text-amber-400" : "text-slate-300";
        const buttonClasses = buttonMode
            ? "rounded-full px-1 py-1 transition hover:scale-105 focus:outline-none"
            : "";
        const attributes = buttonMode
            ? `type="button" data-rating-star="${starValue}" aria-label="Noter ${starValue} sur 5"`
            : 'aria-hidden="true"';

        return `<button ${attributes} class="${buttonClasses} ${starColor} text-3xl font-extrabold leading-none">&#9733;</button>`;
    }).join("");
}

function renderProfileModal() {
    if (!state.profilePreviewArtisan) {
        dom.profileModal.classList.add("hidden");
        dom.profileModal.classList.remove("flex");
        dom.profileModalBody.innerHTML = "";
        return;
    }

    const artisan = state.profilePreviewArtisan;
    const isFavorite = isFavoriteArtisan(artisan);

    dom.profileModal.classList.remove("hidden");
    dom.profileModal.classList.add("flex");
    dom.profileModalBody.innerHTML = `
        <div class="space-y-5">
            <div class="rounded-[28px] bg-slate-950 px-6 py-7 text-white">
                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div>
                        <div class="mb-4 inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] text-amber-200">
                            Profil visible client
                        </div>
                        <h4 class="text-3xl font-extrabold sm:text-4xl">${artisan.artisan_name}</h4>
                        <p class="mt-3 text-sm leading-7 text-slate-300">${artisan.service_type}</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-sm text-slate-300">Note</p>
                            <p class="mt-2 text-xl font-extrabold">${artisan.rating}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-sm text-slate-300">Prix</p>
                            <p class="mt-2 text-xl font-extrabold">${artisan.price} DZD</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-sm text-slate-300">Distance</p>
                            <p class="mt-2 text-xl font-extrabold">${artisan.distance_km !== null ? artisan.distance_km + " km" : "-"}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Nom</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.artisan_name}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Metier</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.service_type}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Commune</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.commune || artisan.city || "Non renseignee"}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Ville</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${artisan.city || artisan.commune || "Non renseignee"}</p>
                </div>
            </div>

            <div class="rounded-3xl bg-slate-50 p-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Description</p>
                <p class="mt-3 text-sm leading-7 text-slate-600">${artisan.description || "Cet artisan n'a pas encore ajoute de description detaillee dans les donnees disponibles."}</p>
            </div>

            <div class="rounded-3xl bg-slate-50 p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Votre notation</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            ${renderRatingStars(state.pendingProfileRating, true)}
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-600">
                            ${state.profileRatingSummary
                                ? `Moyenne actuelle: ${formatAverageRating(state.profileRatingSummary.averageRating)} / 5 • ${state.profileRatingSummary.ratingsCount} avis`
                                : (state.profileRatingLoading ? "Chargement des avis..." : "Aucun avis pour le moment.")}
                        </p>
                    </div>
                    <div class="min-w-[12rem] rounded-2xl bg-white p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Votre note</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-900">${state.pendingProfileRating ? state.pendingProfileRating + "/5" : "Non notee"}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="profile-rating-comment" class="mb-2 block text-sm font-bold text-slate-800">Commentaire optionnel</label>
                    <textarea id="profile-rating-comment" rows="4" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-100" placeholder="Expliquez votre experience avec cet artisan...">${escapeHtml(state.pendingProfileRatingComment)}</textarea>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <button id="profile-rating-submit" type="button" class="rounded-full bg-amber-400 px-5 py-2.5 text-sm font-extrabold text-slate-950 transition hover:bg-amber-300" ${state.profileRatingLoading ? "disabled" : ""}>
                        ${state.profileRatingLoading ? "Enregistrement..." : "Enregistrer ma note"}
                    </button>
                </div>
            </div>

            <div class="rounded-3xl bg-slate-50 p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Travaux realises</p>
                    ${state.profileWorksLoading ? '<span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">Chargement...</span>' : ''}
                </div>
                ${state.profileWorksLoading ?
                    `<p class="mt-3 text-sm text-slate-500">Recuperation des travaux en cours...</p>` :
                    state.profileWorks.length === 0 ?
                        `<p class="mt-3 text-sm leading-7 text-slate-600">Aucun travail disponible pour cet artisan.</p>` :
                        `<div class="space-y-4 mt-4">${state.profileWorks.map(function(work) {
                            return `
                                <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                                    ${work.photoUrl ? `
                                        <div class="relative mb-4 overflow-hidden rounded-2xl">
                                            <img
                                                src="${work.photoUrl}"
                                                alt="${escapeHtml(work.title || "Realisation")}"
                                                class="h-44 w-full rounded-2xl object-cover select-none"
                                                loading="lazy"
                                                draggable="false"
                                                oncontextmenu="return false;"
                                                data-protected-work-image="true"
                                                data-image-preview-src="${work.photoUrl}"
                                                data-image-preview-caption="${escapeHtml(work.title || "Realisation")}"
                                            >
                                            <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-[linear-gradient(180deg,rgba(15,23,42,0)_0%,rgba(15,23,42,0.72)_100%)] px-4 py-3">
                                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-white/90">Photo liee a l'artisan</p>
                                            </div>
                                        </div>
                                    ` : ''}
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-extrabold text-slate-900">${work.title}</p>
                                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">${work.metier || artisan.service_type}</p>
                                        </div>
                                    </div>
                                    <p class="mt-3 text-sm leading-6 text-slate-600">${work.description}</p>
                                </article>
                            `;
                        }).join('')}</div>`}
            </div>

            <div class="flex flex-wrap gap-3">
                <button id="profile-modal-contact" type="button" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800">
                    Contacter cet artisan
                </button>
                <button id="profile-modal-reserve" type="button" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700">
                    Reserver un creneau
                </button>
                <button id="profile-modal-map" type="button" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">
                    Voir sur la carte
                </button>
                <button id="profile-modal-favorite" type="button" class="rounded-full border px-4 py-2 text-sm font-bold transition ${isFavorite ? "border-amber-300 bg-amber-100 text-amber-800 hover:bg-amber-200" : "border-amber-200 bg-white text-amber-700 hover:border-amber-300 hover:bg-amber-50"}">
                    ${isFavorite ? "Retirer des favoris" : "Ajouter aux favoris"}
                </button>
                <a href="${getArtisanProfileUrl(artisan.profile_reference, artisan.artisan_name)}" class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 transition hover:border-amber-300 hover:bg-amber-100">
                    Ouvrir la fiche complete
                </a>
            </div>
        </div>
    `;

    dom.profileModalBody.querySelector("#profile-modal-contact").addEventListener("click", function () {
        selectArtisan(artisan);
    });

    dom.profileModalBody.querySelectorAll("[data-protected-work-image]").forEach(function (image) {
        image.addEventListener("dragstart", function (event) {
            event.preventDefault();
        });
        image.addEventListener("contextmenu", function (event) {
            event.preventDefault();
        });
        image.addEventListener("click", function () {
            openImagePreview(image.dataset.imagePreviewSrc, image.dataset.imagePreviewCaption);
        });
    });

    dom.profileModalBody.querySelectorAll("[data-rating-star]").forEach(function (button) {
        button.addEventListener("click", function () {
            state.pendingProfileRating = Number(button.dataset.ratingStar);
            renderProfileModal();
        });
    });

    const ratingCommentInput = dom.profileModalBody.querySelector("#profile-rating-comment");
    if (ratingCommentInput) {
        ratingCommentInput.addEventListener("input", function (event) {
            state.pendingProfileRatingComment = event.target.value;
        });
    }

    const ratingSubmitButton = dom.profileModalBody.querySelector("#profile-rating-submit");
    if (ratingSubmitButton) {
        ratingSubmitButton.addEventListener("click", submitArtisanRating);
    }

    dom.profileModalBody.querySelector("#profile-modal-reserve").addEventListener("click", function () {
        openReservationComposer(artisan, state.reservationDate || getLocalDateValue(new Date()));
    });

    dom.profileModalBody.querySelector("#profile-modal-map").addEventListener("click", function () {
        closeArtisanProfile();
        setActiveSection("map");
        focusArtisanOnMap(artisan);
    });

    dom.profileModalBody.querySelector("#profile-modal-favorite").addEventListener("click", function () {
        toggleFavoriteArtisan(artisan);
    });
}

function renderMapMarkers() {
    if (!map) {
        return;
    }

    mapMarkers.forEach(function (marker) {
        map.removeLayer(marker);
    });
    mapMarkers = [];

    const usedPositions = [];

    state.artisans.forEach(function (artisan, index) {
        if (!Number.isFinite(artisan.latitude) || !Number.isFinite(artisan.longitude)) {
            return;
        }

        let lat = artisan.latitude;
        let lng = artisan.longitude;

        // Check if position is already used and add small offset
        const positionKey = lat.toFixed(4) + "," + lng.toFixed(4);
        if (usedPositions.includes(positionKey)) {
            // Add small random offset (about 10-50 meters)
            const offset = 0.0001 + Math.random() * 0.0004; // ~10-50 meters
            const angle = Math.random() * 2 * Math.PI;
            lat += offset * Math.cos(angle);
            lng += offset * Math.sin(angle);
        }
        usedPositions.push(positionKey);

        const marker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup(
                '<div class="min-w-[220px] text-sm leading-6 text-slate-700">' +
                (artisan.photo ? '<img src="' + artisan.photo + '" alt="' + artisan.artisan_name + '" class="w-16 h-16 rounded-full object-cover mb-2 border border-slate-200" />' : '') +
                '<strong class="text-slate-900">' + artisan.artisan_name + "</strong><br />" +
                artisan.service_type + "<br />" +
                "Note: " + artisan.rating + "<br />" +
                "Prix: " + artisan.price + " DZD<br />" +
                "Distance: " + (artisan.distance_km !== null ? artisan.distance_km + " km" : "-") + "<br />" +
                "Coordonnées: " + lat.toFixed(4) + ", " + lng.toFixed(4) + "<br />" +
                '<button type="button" onclick="window.clientDashboardOpenProfileFromMap(' + index + ')" class="mt-2 inline-flex rounded-full bg-amber-400 px-3 py-1.5 text-xs font-extrabold text-slate-950">Voir le profil</button>' +
                "</div>"
            );

        marker.on("click", function () {
            state.selectedArtisan = artisan;
            state.messageText = "Bonjour " + artisan.artisan_name + ", je cherche un service de " + artisan.service_type + ". Merci de me confirmer votre disponibilite.";
            state.profilePreviewArtisan = null;
            marker.openPopup();
        });

        mapMarkers.push(marker);
    });
}

function focusArtisanOnMap(artisan) {
    if (map && Number.isFinite(artisan.latitude) && Number.isFinite(artisan.longitude)) {
        map.setView([artisan.latitude, artisan.longitude], 13);
    }
}

function openArtisanProfile(artisan) {
    state.selectedArtisan = artisan;
    state.profilePreviewArtisan = artisan;
    state.profileWorks = [];
    state.profileWorksLoading = true;
    state.profileRatingLoading = true;
    state.profileRatingSummary = null;
    state.pendingProfileRating = 0;
    state.pendingProfileRatingComment = "";
    renderAll();
    loadArtisanWorks(artisan);
    loadArtisanRating(artisan);
}

function closeArtisanProfile() {
    state.profilePreviewArtisan = null;
    state.profileWorks = [];
    state.profileWorksLoading = false;
    state.profileRatingLoading = false;
    state.profileRatingSummary = null;
    state.pendingProfileRating = 0;
    state.pendingProfileRatingComment = "";
    renderProfileModal();
}

async function loadArtisanWorks(artisan) {
    const artisanId = firstDefined(artisan.profile_reference, artisan.artisan_id, artisan.id);

    if (!artisanId) {
        state.profileWorks = [];
        state.profileWorksLoading = false;
        renderProfileModal();
        return;
    }

    state.profileWorksLoading = true;
    renderProfileModal();

    try {
        const params = new URLSearchParams();

        if (artisan.artisan_name) {
            params.set("name", artisan.artisan_name);
        }

        if (artisan.service_type) {
            params.set("service_type", artisan.service_type);
        }

        if (artisan.commune) {
            params.set("commune", artisan.commune);
        }

        if (artisan.city) {
            params.set("city", artisan.city);
        }

        const query = params.toString();
        const response = await fetch(
            `${artisanWorksUrlBase}/${encodeURIComponent(artisanId)}${query ? `?${query}` : ""}`,
            {
            headers: {
                Accept: "application/json",
            },
            }
        );

        if (!response.ok) {
            throw new Error("Impossible de recuperer les travaux de l'artisan.");
        }

        state.profileWorks = await response.json();
    } catch (error) {
        console.error("Error loading artisan works:", error);
        state.profileWorks = [];
    } finally {
        state.profileWorksLoading = false;
        renderProfileModal();
    }
}

async function loadArtisanRating(artisan) {
    const artisanId = firstDefined(artisan?.artisan_id, artisan?.profile_reference, artisan?.id);

    if (!artisanId) {
        state.profileRatingLoading = false;
        state.profileRatingSummary = null;
        renderProfileModal();
        return;
    }

    state.profileRatingLoading = true;
    renderProfileModal();

    try {
        const payload = await fetchJson(
            artisanRatingsBaseUrl + "/" + encodeURIComponent(artisanId) + "/ratings",
            {
                method: "GET",
            },
            "Impossible de charger la note de cet artisan."
        );

        state.profileRatingSummary = payload;
        state.pendingProfileRating = Number(firstDefined(payload?.currentRating, 0)) || 0;
        state.pendingProfileRatingComment = String(firstDefined(payload?.currentComment, ""));
        syncArtisanRatingAcrossState(artisanId, payload?.averageRating);
    } catch (error) {
        state.profileRatingSummary = null;
        state.error = error.message || "Impossible de charger la note de cet artisan.";
        state.success = "";
    } finally {
        state.profileRatingLoading = false;
        renderAll();
    }
}

async function submitArtisanRating() {
    if (!state.profilePreviewArtisan) {
        return;
    }

    if (!state.pendingProfileRating) {
        state.error = "Choisissez une note entre 1 et 5 etoiles.";
        state.success = "";
        renderAlerts();
        return;
    }

    const artisanId = firstDefined(
        state.profilePreviewArtisan.artisan_id,
        state.profilePreviewArtisan.profile_reference,
        state.profilePreviewArtisan.id
    );

    state.profileRatingLoading = true;
    renderProfileModal();

    try {
        const payload = await fetchJson(
            artisanRatingsBaseUrl + "/" + encodeURIComponent(artisanId) + "/ratings",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    rating: state.pendingProfileRating,
                    comment: state.pendingProfileRatingComment.trim(),
                }),
            },
            "Impossible d'enregistrer votre note."
        );

        state.profileRatingSummary = payload;
        state.pendingProfileRating = Number(firstDefined(payload?.currentRating, 0)) || 0;
        state.pendingProfileRatingComment = String(firstDefined(payload?.currentComment, ""));
        syncArtisanRatingAcrossState(artisanId, payload?.averageRating);
        state.error = "";
        state.success = "Votre note a ete enregistree.";
    } catch (error) {
        state.error = error.message || "Impossible d'enregistrer votre note.";
        state.success = "";
    } finally {
        state.profileRatingLoading = false;
        renderAll();
    }
}

function selectArtisan(artisan) {
    state.selectedArtisan = artisan;
    state.profilePreviewArtisan = null;
    state.messageText = "Bonjour " + artisan.artisan_name + ", je suis interesse par votre service de " + artisan.service_type + ". Pouvez-vous me confirmer vos disponibilites ?";
    setActiveSection("messaging");
    focusArtisanOnMap(artisan);
    renderAll();
}

async function persistArtisanRequest(artisan, message) {
    const response = await fetchJson(clientArtisanRequestsStoreUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            artisan_id: artisan.artisan_id,
            message: message,
        }),
    }, "Impossible d'envoyer la notification a l'artisan.");

    return response || {};
}

async function submitRequest() {
    if (!state.selectedArtisan) {
        state.error = "Selectionnez un artisan avant d'envoyer une demande.";
        state.success = "";
        renderAlerts();
        return;
    }

    if (!state.messageText.trim()) {
        state.error = "Entrez un message pour l'artisan.";
        state.success = "";
        renderAlerts();
        return;
    }

    try {
        const notification = await persistArtisanRequest(state.selectedArtisan, state.messageText.trim());
        const nextRequest = {
        id: String(Date.now()),
        notificationId: notification.notificationId || null,
        clientUserId: notification.clientUserId || currentUserId,
        clientName: currentUser,
        artisanName: state.selectedArtisan.artisan_name,
        artisanId: state.selectedArtisan.artisan_id,
        serviceType: state.selectedArtisan.service_type,
        city: state.selectedArtisan.commune || "Skikda",
        price: state.selectedArtisan.price,
        distanceKm: state.selectedArtisan.distance_km,
        message: state.messageText.trim(),
        createdAt: new Date().toISOString(),
        status: "nouvelle",
        chatId: null,
        };

        const existing = JSON.parse(localStorage.getItem(requestsStorageKey) || "[]");
        localStorage.setItem(requestsStorageKey, JSON.stringify([nextRequest].concat(existing)));

        state.error = "";
        state.success = "Votre demande a ete envoyee a l'artisan et par email.";
        syncClientData();
        await syncNotificationData();
        renderAll();
    } catch (error) {
        state.error = error.message || "Impossible d'envoyer la demande.";
        state.success = "";
        renderAlerts();
    }
}

async function sendRequestToArtisan(artisan, message) {
    if (!message.trim()) {
        state.error = "Entrez un message pour l'artisan.";
        state.success = "";
        renderAlerts();
        return;
    }

    try {
        const notification = await persistArtisanRequest(artisan, message.trim());
        const nextRequest = {
        id: String(Date.now()),
        notificationId: notification.notificationId || null,
        clientUserId: notification.clientUserId || currentUserId,
        clientName: currentUser,
        artisanName: artisan.artisan_name,
        artisanId: artisan.artisan_id,
        serviceType: artisan.service_type,
        city: artisan.commune || "Skikda",
        price: artisan.price,
        distanceKm: artisan.distance_km,
        message: message.trim(),
        createdAt: new Date().toISOString(),
        status: "nouvelle",
        chatId: null,
        };

        const existing = JSON.parse(localStorage.getItem(requestsStorageKey) || "[]");
        localStorage.setItem(requestsStorageKey, JSON.stringify([nextRequest].concat(existing)));

        const form = dom.profilesResults.querySelector(`[data-message-form="${state.artisans.indexOf(artisan)}"]`);
        if (form) {
            form.classList.add("hidden");
        }

        state.error = "";
        state.success = "Votre demande a ete envoyee a " + artisan.artisan_name + " et par email.";
        syncClientData();
        await syncNotificationData();
        renderAll();
    } catch (error) {
        state.error = error.message || "Impossible d'envoyer la demande.";
        state.success = "";
        renderAlerts();
    }
}

async function submitReservation() {
    if (!state.selectedArtisan) {
        state.error = "Choisissez un artisan avant de creer une reservation.";
        state.success = "";
        renderAlerts();
        return;
    }

    if (!state.reservationDate) {
        state.error = "Choisissez une date de reservation.";
        state.success = "";
        renderAlerts();
        return;
    }

    if (!state.reservationTime) {
        state.error = "Choisissez une heure de reservation.";
        state.success = "";
        renderAlerts();
        return;
    }

    if (getSelectedArtisanAvailabilityKind(state.reservationDate) !== "available") {
        state.error = "Choisissez un jour disponible chez cet artisan.";
        state.success = "";
        renderAlerts();
        return;
    }

    if (hasReservationForSelectedArtisanDate(state.reservationDate)) {
        state.error = "Vous avez deja reserve cet artisan pour cette date.";
        state.success = "";
        renderAlerts();
        return;
    }

    const reservationDateTime = state.reservationDate + "T" + state.reservationTime;

    if (reservationDateTime < getCurrentDateTimeKey()) {
        state.error = "Choisissez un creneau futur pour la reservation.";
        state.success = "";
        renderAlerts();
        return;
    }

    try {
        await fetchJson(clientReservationsStoreUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                artisan_id: state.selectedArtisan.artisan_id,
                reservation_date: state.reservationDate,
                reservation_time: state.reservationTime,
                notes: state.reservationNotes.trim(),
            }),
        }, "Impossible d'enregistrer la reservation.");

        state.error = "";
        state.success = "Reservation enregistree avec succes.";
        state.reservationNotes = "Intervention souhaitee pour " + state.selectedArtisan.service_type + ".";
        await syncReservationData();
        await syncNotificationData();
        renderAll();
    } catch (error) {
        state.error = error.message || "Impossible d'enregistrer la reservation.";
        state.success = "";
        renderAlerts();
    }
}

async function cancelReservation(reservationId) {
    try {
        await fetchJson(clientReservationsDestroyBaseUrl + "/" + encodeURIComponent(reservationId), {
            method: "DELETE",
        }, "Impossible d'annuler la reservation.");

        state.error = "";
        state.success = "Reservation retiree du planning.";
        await syncReservationData();
        renderAll();
    } catch (error) {
        state.error = error.message || "Impossible d'annuler la reservation.";
        state.success = "";
        renderAlerts();
    }
}

function sendChatMessage(value) {
    const text = String(value || "").trim();

    if (!state.activeChatId || (!text && !state.pendingChatImageDataUrl && !state.pendingChatVoiceDataUrl)) {
        return;
    }

    const storedChats = JSON.parse(localStorage.getItem(chatsStorageKey) || "[]");
    const updatedChats = storedChats.map(function (chat) {
        if (chat.id !== state.activeChatId) {
            return chat;
        }

        return {
            ...chat,
            updatedAt: new Date().toISOString(),
            messages: chat.messages.concat([
                {
                    id: String(Date.now()),
                    sender: "client",
                    senderName: currentUser,
                    text: text,
                    imageDataUrl: state.pendingChatImageDataUrl,
                    imageName: state.pendingChatImageName,
                    voiceDataUrl: state.pendingChatVoiceDataUrl,
                    voiceMimeType: state.pendingChatVoiceMimeType,
                    createdAt: new Date().toISOString(),
                }
            ]),
        };
    });

    if (!persistClientChats(updatedChats)) {
        return;
    }

    clearPendingChatAttachments();
    state.error = "";
    state.success = "";
    syncClientData();
    renderAll();
}

function syncClientData() {
    const storedRequests = JSON.parse(localStorage.getItem(requestsStorageKey) || "[]");
    const storedChats = JSON.parse(localStorage.getItem(chatsStorageKey) || "[]");
    const clientRequests = storedRequests.filter(function (request) {
        return request.clientName === currentUser;
    });
    const accepted = clientRequests.filter(function (request) {
        return request.status === "acceptee";
    });

    state.requestsCount = clientRequests.length;
    state.acceptedRequests = accepted;

    const acceptedWithChat = accepted.find(function (request) {
        return request.chatId;
    });
    const availableChat = acceptedWithChat ? acceptedWithChat.chatId : "";

    if (!state.activeChatId || !accepted.some(function (request) { return request.chatId === state.activeChatId; })) {
        state.activeChatId = availableChat;
    }

    const currentChat = storedChats.find(function (chat) {
        return chat.id === state.activeChatId;
    });
    state.chatMessages = currentChat ? currentChat.messages.map(normalizeChatMessage) : [];
}

async function syncReservationData() {
    const reservations = await fetchJson(
        clientReservationsIndexUrl,
        {
            method: "GET",
        },
        "Impossible de charger vos reservations."
    );

    const normalizedReservations = (Array.isArray(reservations) ? reservations : []).map(normalizeReservation);
    state.reservations = sortReservations(uniqueReservations(normalizedReservations));

    if (!state.reservationDate) {
        state.reservationDate = getLocalDateValue(new Date());
    }
}

async function syncNotificationData() {
    const payload = await fetchJson(
        notificationsIndexUrl,
        {
            method: "GET",
        },
        "Impossible de charger vos notifications."
    );

    state.notifications = Array.isArray(payload.notifications) ? payload.notifications : [];
    state.unreadNotificationsCount = payload.unreadCount || 0;
}

function applyRecommendedProfiles() {
    state.artisans = state.recommendedArtisans.slice();
    state.resultsTitle = state.activeServiceType
        ? "Profils recommandes pour " + state.activeServiceType
        : "Profils recommandes";
    state.profilesSearchLoading = false;
    state.error = "";

    if (!state.selectedArtisan || !state.artisans.some(function (artisan) {
        return artisan.artisan_id === state.selectedArtisan.artisan_id;
    })) {
        state.selectedArtisan = state.artisans[0] || null;
    }
}

function resetProfileFilters() {
    state.profilesSearchQuery = "";
    state.profilesSearchService = "";
    state.profilesSearchCommune = "";
    state.profilesSearchMinPrice = "";
    state.profilesSearchMaxPrice = "";
    state.profilesSearchMinRating = "";
    state.profilesSearchMaxRating = "";

    if (dom.profilesSearch) {
        dom.profilesSearch.value = "";
    }

    if (dom.profilesService) {
        dom.profilesService.value = "";
    }

    if (dom.profilesCommune) {
        dom.profilesCommune.value = "";
    }

    if (dom.profilesMinPrice) {
        dom.profilesMinPrice.value = "";
    }

    if (dom.profilesMaxPrice) {
        dom.profilesMaxPrice.value = "";
    }

    if (dom.profilesMinRating) {
        dom.profilesMinRating.value = "";
    }

    if (dom.profilesMaxRating) {
        dom.profilesMaxRating.value = "";
    }

    profilesSearchRequestId += 1;
    searchProfilesByName();
}

function loadProfilesSearchHistory() {
    const stored = window.localStorage.getItem(profilesSearchHistoryStorageKey);
    if (!stored) {
        state.profilesSearchHistory = [];
        return;
    }

    try {
        state.profilesSearchHistory = JSON.parse(stored) || [];
    } catch (error) {
        state.profilesSearchHistory = [];
    }
}

function saveProfilesSearchHistory() {
    window.localStorage.setItem(profilesSearchHistoryStorageKey, JSON.stringify(state.profilesSearchHistory));
}

function getArtisanFavoriteKey(artisan) {
    return String(firstDefined(artisan.profile_reference, artisan.artisan_id, artisan.id, artisan.artisan_name));
}

function loadFavoriteArtisans() {
    const stored = window.localStorage.getItem(favoriteArtisansStorageKey);
    if (!stored) {
        state.favoriteArtisans = [];
        return;
    }

    try {
        state.favoriteArtisans = JSON.parse(stored) || [];
    } catch (error) {
        state.favoriteArtisans = [];
    }
}

function saveFavoriteArtisans() {
    window.localStorage.setItem(favoriteArtisansStorageKey, JSON.stringify(state.favoriteArtisans));
}

function isFavoriteArtisan(artisan) {
    const artisanKey = getArtisanFavoriteKey(artisan);
    return state.favoriteArtisans.some(function (favorite) {
        return getArtisanFavoriteKey(favorite) === artisanKey;
    });
}

function toggleFavoriteArtisan(artisan) {
    const artisanKey = getArtisanFavoriteKey(artisan);
    const favoriteIndex = state.favoriteArtisans.findIndex(function (favorite) {
        return getArtisanFavoriteKey(favorite) === artisanKey;
    });

    if (favoriteIndex >= 0) {
        state.favoriteArtisans.splice(favoriteIndex, 1);
        state.success = artisan.artisan_name + " retire de vos favoris.";
    } else {
        state.favoriteArtisans.unshift(artisan);
        state.success = artisan.artisan_name + " ajoute a vos favoris.";
    }

    state.error = null;
    saveFavoriteArtisans();
    renderAll();
}

function removeFavoriteArtisan(artisanKey) {
    const favorite = state.favoriteArtisans.find(function (item) {
        return getArtisanFavoriteKey(item) === artisanKey;
    });
    state.favoriteArtisans = state.favoriteArtisans.filter(function (item) {
        return getArtisanFavoriteKey(item) !== artisanKey;
    });
    state.success = favorite ? favorite.artisan_name + " retire de vos favoris." : "Favori retire.";
    state.error = null;
    saveFavoriteArtisans();
    renderAll();
}

function renderFavoriteArtisans() {
    if (!dom.favoriteArtisansList) {
        return;
    }

    if (dom.favoriteArtisansCount) {
        dom.favoriteArtisansCount.textContent = String(state.favoriteArtisans.length);
    }

    dom.favoriteArtisansList.innerHTML = state.favoriteArtisans.length
        ? state.favoriteArtisans.map(function (artisan) {
            const artisanKey = escapeHtml(getArtisanFavoriteKey(artisan));
            return `
                <div class="rounded-xl border border-amber-200 bg-white p-3">
                    <p class="text-sm font-extrabold text-slate-900">${escapeHtml(artisan.artisan_name)}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500">${escapeHtml(artisan.service_type || "Artisan")}</p>
                    <div class="mt-3 flex gap-2">
                        <button type="button" data-favorite-open="${artisanKey}" class="flex-1 rounded-full bg-amber-400 px-3 py-2 text-xs font-extrabold text-slate-950 transition hover:bg-amber-300">
                            Profil
                        </button>
                        <button type="button" data-favorite-remove="${artisanKey}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-500 transition hover:border-rose-300 hover:text-rose-600">
                            Retirer
                        </button>
                    </div>
                </div>
            `;
        }).join("")
        : '<div class="rounded-xl bg-white p-3 text-sm text-slate-500">Aucun favori pour le moment.</div>';
}

function addProfilesSearchHistoryEntry(query) {
    const trimmedQuery = query.trim();
    if (!trimmedQuery) {
        return;
    }

    state.profilesSearchHistory = state.profilesSearchHistory.filter(function (item) {
        return item.toLowerCase() !== trimmedQuery.toLowerCase();
    });
    state.profilesSearchHistory.unshift(trimmedQuery);
    if (state.profilesSearchHistory.length > 5) {
        state.profilesSearchHistory.length = 5;
    }
    saveProfilesSearchHistory();
}

function clearProfilesSearchHistory() {
    state.profilesSearchHistory = [];
    saveProfilesSearchHistory();
    renderSearchHistory();
}

function renderSearchHistory() {
    if (!dom.profilesSearchHistoryBlock || !dom.profilesSearchHistoryList) {
        return;
    }

    dom.profilesSearchHistoryList.innerHTML = state.profilesSearchHistory.length
        ? state.profilesSearchHistory.map(function (term) {
            return '<button type="button" data-history-query="' + escapeHtml(term) + '" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-sm font-medium text-slate-900 transition hover:border-slate-300 hover:bg-slate-50">' + escapeHtml(term) + '</button>';
        }).join("")
        : '<div class="rounded-xl bg-white p-3 text-sm text-slate-500">Aucune recherche recente.</div>';

    if (dom.profilesSearchHistoryClear) {
        dom.profilesSearchHistoryClear.classList.toggle("hidden", !state.profilesSearchHistory.length);
    }

    if (dom.profilesSearchHistoryCount) {
        dom.profilesSearchHistoryCount.textContent = String(state.profilesSearchHistory.length);
    }
}

async function searchProfilesByName() {
    const query = state.profilesSearchQuery.trim();
    if (query) {
        addProfilesSearchHistoryEntry(query);
        renderSearchHistory();
    }

    const serviceQuery = state.profilesSearchService.trim();
    const hasFilters = Boolean(
        serviceQuery ||
        state.profilesSearchCommune ||
        state.profilesSearchMinPrice ||
        state.profilesSearchMaxPrice ||
        state.profilesSearchMinRating ||
        state.profilesSearchMaxRating
    );

    if (!query && !hasFilters) {
        profilesSearchRequestId += 1;
        applyRecommendedProfiles();
        renderAll();
        renderMapMarkers();
        return;
    }

    const requestId = ++profilesSearchRequestId;
    state.profilesSearchLoading = true;
    state.error = "";
    state.success = "";
    if (query || serviceQuery || state.profilesSearchCommune) {
        state.resultsTitle = "Recherche personnalisee";
    } else {
        state.resultsTitle = state.activeServiceType
            ? 'Recherche par filtres dans ' + state.activeServiceType
            : 'Recherche par filtres';
    }
    renderAll();

    try {
        const params = new URLSearchParams();

        if (query) {
            params.set('name', query);
        }

        if (serviceQuery || state.activeServiceType) {
            params.set('service_type', serviceQuery || state.activeServiceType);
        }

        if (state.profilesSearchCommune) {
            params.set('commune', state.profilesSearchCommune);
        }

        if (state.profilesSearchMinPrice) {
            params.set('min_price', state.profilesSearchMinPrice);
        }

        if (state.profilesSearchMaxPrice) {
            params.set('max_price', state.profilesSearchMaxPrice);
        }

        if (state.profilesSearchMinRating) {
            params.set('min_rating', state.profilesSearchMinRating);
        }

        if (state.profilesSearchMaxRating) {
            params.set('max_rating', state.profilesSearchMaxRating);
        }

        const response = await fetch(searchArtisansByNameUrl + "?" + params.toString(), {
            headers: {
                Accept: "application/json",
            },
        });

        if (!response.ok) {
            throw new Error("Impossible de rechercher les artisans par nom.");
        }

        const data = await response.json();

        if (requestId !== profilesSearchRequestId) {
            return;
        }

        state.artisans = Array.isArray(data) ? data.map(normalizeArtisan) : [];
        if (query || serviceQuery || state.profilesSearchCommune) {
            const titleParts = [];
            if (query) {
                titleParts.push('nom "' + query + '"');
            }
            if (serviceQuery) {
                titleParts.push('service "' + serviceQuery + '"');
            }
            if (state.profilesSearchCommune) {
                titleParts.push('commune "' + state.profilesSearchCommune + '"');
            }
            state.resultsTitle = "Resultats pour " + titleParts.join(", ");
        } else {
            state.resultsTitle = 'Resultats filtres' + (state.activeServiceType ? ' dans ' + state.activeServiceType : '');
        }
        state.profilesSearchLoading = false;

        if (!state.selectedArtisan || !state.artisans.some(function (artisan) {
            return artisan.artisan_id === state.selectedArtisan.artisan_id;
        })) {
            state.selectedArtisan = state.artisans[0] || null;

            if (state.selectedArtisan) {
                state.messageText = "Bonjour " + state.selectedArtisan.artisan_name + ", je suis interesse par votre service de " + state.selectedArtisan.service_type + ". Pouvez-vous me confirmer vos disponibilites ?";
            }
        }

        renderAll();
        renderMapMarkers();
    } catch (error) {
        if (requestId !== profilesSearchRequestId) {
            return;
        }

        state.profilesSearchLoading = false;
        state.error = error.message || "Impossible de rechercher les artisans par nom.";
        renderAlerts();
        renderProfiles();
    }
}

async function searchByService() {
    const service = dom.service.value.trim();

    if (!service) {
        state.error = "Entrez un service.";
        state.success = "";
        renderAlerts();
        return;
    }

    if (!state.position) {
        await initGeolocation();

        if (!state.position) {
            state.error = state.error || "Position non disponible.";
            state.success = "";
            renderAlerts();
            return;
        }
    }

    addProfilesSearchHistoryEntry(service);
    renderSearchHistory();

    state.isLoading = true;
    state.error = "";
    state.success = "";
    dom.searchByServiceButton.textContent = "Recherche en cours...";
    renderAlerts();

    try {
        const params = new URLSearchParams({
            service_type: service,
            lat: String(state.position.lat),
            lon: String(state.position.lng),
        });

        const response = await fetch(recommendUrl + "?" + params.toString(), {
            headers: {
                Accept: "application/json",
            },
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || "Erreur lors de l'appel API.");
        }

        state.artisans = (data.meilleurs_artisans || []).map(normalizeArtisan).sort(sortArtisansByScoreDesc);
        state.recommendedArtisans = state.artisans.slice();
        state.complementaryServices = (data.services_complementaires || []).map(normalizeArtisan);
        state.showComplementaryServices = false;
        state.activeServiceType = service;
        state.profilesSearchQuery = "";
        state.profilesSearchLoading = false;
        state.resultsTitle = "Profils recommandes pour " + service;
        state.searchSummary = "Recherche intelligente locale";
        state.profilePreviewArtisan = null;
        profilesSearchRequestId += 1;

        if (dom.profilesSearch) {
            dom.profilesSearch.value = "";
        }

        if (state.artisans.length > 0) {
            state.selectedArtisan = state.artisans[0];
            state.messageText = "Bonjour " + state.artisans[0].artisan_name + ", je souhaite discuter de votre prestation de " + state.artisans[0].service_type + ". Pouvez-vous me recontacter ?";
            setActiveSection("profiles");
        } else {
            state.selectedArtisan = null;
            state.messageText = "";
            state.error = data.message || "Aucun artisan trouve pour ce service.";
        }
    } catch (error) {
        state.artisans = [];
        state.recommendedArtisans = [];
        state.complementaryServices = [];
        state.showComplementaryServices = false;
        state.activeServiceType = service;
        state.profilesSearchLoading = false;
        state.selectedArtisan = null;
        state.profilePreviewArtisan = null;
        state.messageText = "";
        state.error = error.message || "Impossible de recuperer les recommandations.";
    } finally {
        state.isLoading = false;
        dom.searchByServiceButton.textContent = "Trouver des artisans";
        renderAll();
        renderMapMarkers();
    }
}

function initMap() {
    if (typeof L === "undefined") {
        return;
    }

    map = L.map("map").setView([28.0339, 1.6596], 5);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);
}

function getGeolocationErrorMessage(error) {
    const isLocalhost = ["localhost", "127.0.0.1", "::1"].includes(window.location.hostname);

    if (!window.isSecureContext && !isLocalhost) {
        return "La geolocalisation du navigateur exige HTTPS sur ce domaine.";
    }

    if (!error) {
        return "Impossible de recuperer votre position actuelle.";
    }

    if (error.code === 1) {
        return "Acces a la position refuse. Autorisez la localisation dans le navigateur puis reessayez.";
    }

    if (error.code === 2) {
        return "Position indisponible pour le moment. Verifiez que votre GPS est active, que vous etes connecte a un reseau Wi-Fi ou mobile, puis rafraichissez la page.";
    }

    if (error.code === 3) {
        return "La detection de position prend trop de temps. Verifiez votre connexion internet et GPS, puis rafraichissez la page pour reessayer.";
    }

    return "Impossible de recuperer votre position actuelle.";
}

function initGeolocation() {
    if (!navigator.geolocation) {
        state.error = "La geolocalisation n'est pas supportee par ce navigateur.";
        state.geoPending = false;
        renderAlerts();
        renderHeaderStats();
        return Promise.resolve(null);
    }

    if (geolocationRequest) {
        return geolocationRequest;
    }

    state.geoPending = true;
    state.error = "";
    renderAlerts();
    renderNotifications();
    renderHeaderStats();

    geolocationRequest = new Promise(function (resolve) {
        function tryGeolocation(highAccuracy) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    state.position = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    state.geoPending = false;
                    state.error = "";

                    if (map) {
                        if (clientMarker) {
                            map.removeLayer(clientMarker);
                        }

                        clientMarker = L.marker([state.position.lat, state.position.lng])
                            .addTo(map)
                            .bindPopup("Vous etes ici")
                            .openPopup();

                        map.setView([state.position.lat, state.position.lng], 12);
                    }

                    geolocationRequest = null;
                    renderHeaderStats();
                    renderAlerts();
                    resolve(state.position);
                },
                function (error) {
                    // Si la haute précision échoue, essayer avec précision normale
                    if (highAccuracy && error.code === 2) {
                        tryGeolocation(false);
                        return;
                    }

                    state.geoPending = false;
                    state.error = getGeolocationErrorMessage(error);
                    geolocationRequest = null;
                    renderAlerts();
                    renderHeaderStats();
                    resolve(null);
                },
                {
                    enableHighAccuracy: highAccuracy,
                    timeout: highAccuracy ? 5000 : 20000,
                    maximumAge: highAccuracy ? 0 : 300000,
                }
            );
        }

        tryGeolocation(true);
    });

    return geolocationRequest;
}

function renderAll() {
    renderAlerts();
    renderHeaderStats();
    renderProfiles();
    renderMessaging();
    renderChat();
    renderReservations();
    renderProfileModal();
}

function openImagePreview(src, caption) {
    if (!src) {
        return;
    }

    dom.imagePreviewImg.src = src;
    dom.imagePreviewImg.alt = caption || "Apercu image";
    dom.imagePreviewCaption.textContent = caption || "";
    dom.imagePreviewModal.classList.remove("hidden");
    dom.imagePreviewModal.classList.add("flex");
    document.body.classList.add("overflow-hidden");
}

function closeImagePreview() {
    dom.imagePreviewModal.classList.add("hidden");
    dom.imagePreviewModal.classList.remove("flex");
    dom.imagePreviewImg.src = "";
    dom.imagePreviewImg.alt = "";
    dom.imagePreviewCaption.textContent = "";
    document.body.classList.remove("overflow-hidden");
}

function bindDom() {
    dom.alerts = document.getElementById("alerts");
    dom.requestsCountBadge = document.getElementById("requests-count-badge");
    dom.reservationsCountBadge = document.getElementById("reservations-count-badge");
    dom.heroResultsCount = document.getElementById("hero-results-count");
    dom.heroSelectedCount = document.getElementById("hero-selected-count");
    dom.heroPositionCount = document.getElementById("hero-position-count");
    dom.geoIndicator = document.getElementById("geo-indicator");
    dom.geoStatus = document.getElementById("geo-status");
    dom.positionLabel = document.getElementById("position-label");
    dom.actionLabel = document.getElementById("action-label");
    dom.resultsTitle = document.getElementById("results-title");
    dom.profilesCountBadge = document.getElementById("profiles-count-badge");
    dom.mapPointsBadge = document.getElementById("map-points-badge");
    dom.chatCountBadge = document.getElementById("chat-count-badge");
    dom.profilesResults = document.getElementById("profiles-results");
    dom.complementaryServicesBlock = document.getElementById("complementary-services-block");
    dom.messagingContent = document.getElementById("messaging-content");
    dom.chatContent = document.getElementById("chat-content");
    dom.reservationsContent = document.getElementById("reservations-content");
    dom.reservationsCalendar = document.getElementById("reservations-calendar");
    dom.reservationsList = document.getElementById("reservations-list");
    dom.reservationsListBadge = document.getElementById("reservations-list-badge");
    dom.reservationNextBadge = document.getElementById("reservation-next-badge");
    dom.notificationsCountBadge = document.getElementById("notifications-count-badge");
    dom.notificationsList = document.getElementById("notifications-list");
    dom.notificationsReadButton = document.getElementById("notifications-read-button");
    dom.calendarTitle = document.getElementById("calendar-title");
    dom.calendarPrevMonth = document.getElementById("calendar-prev-month");
    dom.calendarNextMonth = document.getElementById("calendar-next-month");
    dom.profileModal = document.getElementById("profile-modal");
    dom.profileModalBody = document.getElementById("profile-modal-body");
    dom.profileModalOverlay = document.getElementById("profile-modal-overlay");
    dom.closeProfileModal = document.getElementById("close-profile-modal");
    dom.imagePreviewModal = document.getElementById("image-preview-modal");
    dom.imagePreviewOverlay = document.getElementById("image-preview-overlay");
    dom.imagePreviewClose = document.getElementById("image-preview-close");
    dom.imagePreviewImg = document.getElementById("image-preview-img");
    dom.imagePreviewCaption = document.getElementById("image-preview-caption");
    dom.searchByServiceButton = document.getElementById("search-by-service-button");
    dom.service = document.getElementById("service");
    dom.profilesSearch = document.getElementById("profiles-search");
    dom.profilesService = document.getElementById("profiles-service");
    dom.profilesSearchHistoryBlock = document.getElementById("profiles-search-history-block");
    dom.profilesSearchHistoryList = document.getElementById("profiles-search-history-list");
    dom.profilesSearchHistoryClear = document.getElementById("profiles-search-history-clear");
    dom.profilesSearchHistoryCount = document.getElementById("profiles-search-history-count");
    dom.favoriteArtisansBlock = document.getElementById("favorite-artisans-block");
    dom.favoriteArtisansList = document.getElementById("favorite-artisans-list");
    dom.favoriteArtisansCount = document.getElementById("favorite-artisans-count");
    dom.profilesCommune = document.getElementById("profiles-commune");
    dom.profilesMinPrice = document.getElementById("profiles-min-price");
    dom.profilesMaxPrice = document.getElementById("profiles-max-price");
    dom.profilesMinRating = document.getElementById("profiles-min-rating");
    dom.profilesMaxRating = document.getElementById("profiles-max-rating");
    dom.clientActionsMenuButton = document.getElementById("client-actions-menu-button");
    dom.clientActionsMenu = document.getElementById("client-actions-menu");
    dom.clientMenuReservations = document.getElementById("client-menu-reservations");
    dom.clientMenuRequests = document.getElementById("client-menu-requests");
    dom.clientMenuLogout = document.getElementById("client-menu-logout");
    dom.clientMenuReservationsCount = document.getElementById("client-menu-reservations-count");
    dom.clientMenuRequestsCount = document.getElementById("client-menu-requests-count");
    dom.profilesResetFilters = document.getElementById("profiles-reset-filters");
    dom.sectionButtons = Array.from(document.querySelectorAll("[data-section-button]"));
    dom.sectionSearch = document.getElementById("section-search");
    dom.searchMapCard = document.getElementById("search-map-card");
    dom.sectionMap = document.getElementById("section-map");
    dom.sectionProfiles = document.getElementById("section-profiles");
    dom.sectionMessaging = document.getElementById("section-messaging");
    dom.sectionChat = document.getElementById("section-chat");
    dom.sectionReservations = document.getElementById("section-reservations");
}

function initEvents() {
    dom.searchByServiceButton.addEventListener("click", searchByService);
    if (dom.notificationsReadButton) {
        dom.notificationsReadButton.addEventListener("click", async function () {
            try {
                await fetchJson(notificationsReadUrl, {
                    method: "PATCH",
                }, "Impossible de marquer les notifications comme lues.");
                await syncNotificationData();
                renderNotifications();
            } catch (error) {
                state.error = error.message || "Impossible de marquer les notifications comme lues.";
                renderAlerts();
            }
        });
    }

    dom.profilesSearch.addEventListener("input", function (event) {
        state.profilesSearchQuery = event.target.value;
        if (profilesSearchTimeout) {
            window.clearTimeout(profilesSearchTimeout);
        }

        profilesSearchTimeout = window.setTimeout(function () {
            searchProfilesByName();
        }, 250);
    });

    [
        dom.profilesService,
        dom.profilesCommune,
        dom.profilesMinPrice,
        dom.profilesMaxPrice,
        dom.profilesMinRating,
        dom.profilesMaxRating,
    ].forEach(function (input) {
        if (!input) {
            return;
        }

        const handleProfileFilterChange = function (event) {
            const value = event.target.value.trim();

            switch (event.target.id) {
                case "profiles-service":
                    state.profilesSearchService = value;
                    break;
                case "profiles-commune":
                    state.profilesSearchCommune = value;
                    break;
                case "profiles-min-price":
                    state.profilesSearchMinPrice = value;
                    break;
                case "profiles-max-price":
                    state.profilesSearchMaxPrice = value;
                    break;
                case "profiles-min-rating":
                    state.profilesSearchMinRating = value;
                    break;
                case "profiles-max-rating":
                    state.profilesSearchMaxRating = value;
                    break;
            }

            if (profilesSearchTimeout) {
                window.clearTimeout(profilesSearchTimeout);
            }

            profilesSearchTimeout = window.setTimeout(function () {
                searchProfilesByName();
            }, 250);
        };

        input.addEventListener("input", handleProfileFilterChange);
        input.addEventListener("change", handleProfileFilterChange);
    });

    dom.sectionButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            setActiveSection(button.dataset.sectionButton);
        });
    });

    if (dom.clientActionsMenuButton && dom.clientActionsMenu) {
        dom.clientActionsMenuButton.addEventListener("click", function (event) {
            event.stopPropagation();
            dom.clientActionsMenu.classList.toggle("hidden");
        });

        document.addEventListener("click", function (event) {
            if (!dom.clientActionsMenu.contains(event.target) && event.target !== dom.clientActionsMenuButton) {
                dom.clientActionsMenu.classList.add("hidden");
            }
        });
    }

    if (dom.clientMenuReservations) {
        dom.clientMenuReservations.addEventListener("click", function () {
            const menu = dom.clientMenuReservations.closest("details");
            if (menu) {
                menu.removeAttribute("open");
            }
            setActiveSection("reservations");
            if (dom.clientActionsMenu) {
                dom.clientActionsMenu.classList.add("hidden");
            }
        });
    }

    if (dom.clientMenuRequests) {
        dom.clientMenuRequests.addEventListener("click", function () {
            setActiveSection("search");
            state.success = state.requestsCount > 0 ? "Vous avez " + state.requestsCount + " demande(s) prête(s)." : "Aucune demande préparée pour le moment.";
            state.error = null;
            renderAlerts();
            dom.clientActionsMenu.classList.add("hidden");
        });
    }

    if (dom.clientMenuLogout) {
        dom.clientMenuLogout.addEventListener("click", function () {
            document.getElementById('logout-form').submit();
        });
    }

    if (dom.profilesResetFilters) {
        dom.profilesResetFilters.addEventListener("click", function () {
            resetProfileFilters();
        });
    }

    if (dom.profilesSearchHistoryClear) {
        dom.profilesSearchHistoryClear.addEventListener("click", function () {
            clearProfilesSearchHistory();
        });
    }

    if (dom.profilesSearchHistoryList) {
        dom.profilesSearchHistoryList.addEventListener("click", function (event) {
            const button = event.target.closest("[data-history-query]");
            if (!button) {
                return;
            }

            const query = button.dataset.historyQuery;
            dom.profilesSearch.value = query;
            state.profilesSearchQuery = query;
            setActiveSection("profiles");
            const menu = button.closest("details");
            if (menu) {
                menu.removeAttribute("open");
            }
            searchProfilesByName();
        });
    }

    if (dom.favoriteArtisansList) {
        dom.favoriteArtisansList.addEventListener("click", function (event) {
            const openButton = event.target.closest("[data-favorite-open]");
            const removeButton = event.target.closest("[data-favorite-remove]");

            if (openButton) {
                const artisanKey = openButton.dataset.favoriteOpen;
                const artisan = state.favoriteArtisans.find(function (item) {
                    return getArtisanFavoriteKey(item) === artisanKey;
                });

                if (artisan) {
                    document.querySelectorAll("header details").forEach(function (menu) {
                        menu.removeAttribute("open");
                    });
                    openArtisanProfile(artisan);
                }
            }

            if (removeButton) {
                removeFavoriteArtisan(removeButton.dataset.favoriteRemove);
            }
        });
    }

    dom.profileModalOverlay.addEventListener("click", closeArtisanProfile);
    dom.closeProfileModal.addEventListener("click", closeArtisanProfile);
    dom.imagePreviewOverlay.addEventListener("click", closeImagePreview);
    dom.imagePreviewClose.addEventListener("click", closeImagePreview);
    dom.calendarPrevMonth.addEventListener("click", function () {
        state.calendarCursor = new Date(state.calendarCursor.getFullYear(), state.calendarCursor.getMonth() - 1, 1);
        renderReservations();
        renderHeaderStats();
    });
    dom.calendarNextMonth.addEventListener("click", function () {
        state.calendarCursor = new Date(state.calendarCursor.getFullYear(), state.calendarCursor.getMonth() + 1, 1);
        renderReservations();
        renderHeaderStats();
    });

    window.addEventListener("storage", function (event) {
        if (event.key === requestsStorageKey || event.key === chatsStorageKey) {
            syncClientData();
            renderAll();
        }

        if (event.key === favoriteArtisansStorageKey) {
            loadFavoriteArtisans();
            renderAll();
        }
    });

    window.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeImagePreview();
        }
    });
}

async function init() {
    state.reservationDate = getLocalDateValue(new Date());
    bindDom();
    initEvents();

    const params = new URLSearchParams(window.location.search);
    const requestedSection = params.get("section");

    setActiveSection("search");
    if (requestedSection === "reservations") {
        setActiveSection("reservations");
    }
    syncClientData();
    try {
        await syncReservationData();
        await syncNotificationData();
    } catch (error) {
        state.error = error.message || "Impossible de charger vos donnees.";
    }
    initMap();
    initGeolocation();
    loadProfilesSearchHistory();
    loadFavoriteArtisans();
    renderAll();

    if (params.toString()) {
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

window.clientDashboardOpenProfileFromMap = function (index) {
    const artisan = state.artisans[index];
    if (!artisan) {
        return;
    }

    openArtisanProfile(artisan);
};

document.addEventListener("DOMContentLoaded", function () {
    init();
});
</script>
</body>
</html>
