<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="/favicon.png">
<title>Espace Artisan</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#eef4ff_45%,#ffffff_100%)] text-slate-900" style="font-family: 'Manrope', sans-serif;">
@php
    $avatar = null;
    $currentService = $artisanService ?? (auth()->user()->artisan?->service_type ?? $profile->metier);
    if (($profile->photo ?? null)) {
        $avatar = asset('storage/' . $profile->photo);
    } elseif (auth()->user()->photo ?? null) {
        $avatar = asset('storage/' . auth()->user()->photo);
    }
@endphp

<main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <header class="mb-6 flex flex-col gap-4 rounded-[30px] border border-white/70 bg-white/80 px-5 py-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur md:flex-row md:items-center md:justify-between md:px-7">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-slate-900 text-lg font-extrabold text-white">
                @if($avatar)
                    <img src="{{ $avatar }}" alt="Avatar" class="h-full w-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>

            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.28em] text-indigo-700">Espace artisan</p>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">{{ auth()->user()->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">Pilotez votre profil, vos demandes clients et vos actions prioritaires.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <details class="group relative inline-block text-left">
                <summary class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    Notifications
                    <span id="artisan-notifications-count-badge" class="rounded-full bg-rose-600 px-2 py-0.5 text-xs font-extrabold text-white">0</span>
                </summary>
                <div class="absolute right-0 z-40 mt-2 w-80 rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_18px_40px_rgba(15,23,42,0.14)]">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <p class="text-sm font-extrabold text-slate-900">Notifications</p>
                        <button id="artisan-notifications-read-button" type="button" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-700">Lu</button>
                    </div>
                    <div id="artisan-notifications-list" class="grid max-h-80 gap-2 overflow-y-auto pr-1"></div>
                </div>
            </details>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-full bg-rose-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-rose-700">
                    Déconnexion
                </button>
            </form>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div id="artisan-feedback" class="hidden mb-5 rounded-2xl px-5 py-4 text-sm font-semibold"></div>

    <section class="rounded-[32px] bg-slate-950 px-6 py-7 text-white shadow-[0_28px_80px_rgba(15,23,42,0.20)] sm:px-8">
        <div>
            <div class="mb-5 inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] text-indigo-200">
                Tableau de bord artisan
            </div>
            <h2 class="max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl">
                Mettez votre profil a jour et gerez les demandes clients.
            </h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                Votre interface artisan regroupe les informations de profil, les leads recus depuis l'espace client et les prochaines actions a traiter.
            </p>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-300">Service</p>
                    <p class="mt-2 text-xl font-extrabold">{{ $currentService ?: 'Non renseigne' }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-300">Experience</p>
                    <p class="mt-2 text-xl font-extrabold">{{ $profile->experience ?? 0 }} ans</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-300">Reception</p>
                    <p id="request-count-hero" class="mt-2 text-xl font-extrabold">0 demande</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 overflow-x-auto rounded-[28px] border border-white/70 bg-white/85 p-3 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur">
        <div class="flex min-w-max gap-3">
            <button type="button" data-artisan-section-button="profile" class="rounded-2xl px-4 py-3 text-left transition">
                <p class="text-sm font-extrabold">Profil</p>
                <p class="mt-1 text-xs">Mettre a jour vos infos</p>
            </button>
            <button type="button" data-artisan-section-button="portfolio" class="rounded-2xl px-4 py-3 text-left transition">
                <p class="text-sm font-extrabold">Realisations</p>
                <p class="mt-1 text-xs">Gerer vos travaux</p>
            </button>
            <button type="button" data-artisan-section-button="requests" class="rounded-2xl px-4 py-3 text-left transition">
                <p class="text-sm font-extrabold">Demandes</p>
                <p class="mt-1 text-xs">Voir les clients</p>
            </button>
            <button type="button" data-artisan-section-button="reservations" class="rounded-2xl px-4 py-3 text-left transition">
                <p class="text-sm font-extrabold">Reservations</p>
                <p class="mt-1 text-xs">Rendez-vous confirms</p>
            </button>
            <button type="button" data-artisan-section-button="chat" class="rounded-2xl px-4 py-3 text-left transition">
                <p class="text-sm font-extrabold">Chat</p>
                <p class="mt-1 text-xs">Suivre les discussions</p>
            </button>
        </div>
    </section>

    <section id="artisan-section-profile" class="mt-6">
        <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-teal-700">Profil</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Modifier le profil artisan</h3>
                </div>
                <span class="rounded-full bg-teal-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-teal-800">
                    Menu profil
                </span>
            </div>

            <form method="POST" action="{{ route('artisan.upload') }}" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="service-display" class="mb-2 block text-sm font-bold text-slate-800">Service principal</label>
                        <input
                            id="service-display"
                            type="text"
                            value="{{ $currentService ?: 'Aucun service trouve dans la base artisan' }}"
                            readonly
                            class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-4 text-sm font-medium text-slate-900 outline-none"
                        >
                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Ce service vient automatiquement de la base de donnees artisan et n'est plus modifiable ici.
                        </p>
                    </div>

                    <div>
                        <label for="experience" class="mb-2 block text-sm font-bold text-slate-800">Experience</label>
                        <input
                            id="experience"
                            type="number"
                            min="0"
                            name="experience"
                            value="{{ $profile->experience ?? 0 }}"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-900 outline-none transition focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                            placeholder="Nombre d'annees d'experience"
                        >
                    </div>

                    <div>
                        <label for="photo" class="mb-2 block text-sm font-bold text-slate-800">Photo de profil</label>
                        <input
                            id="photo"
                            type="file"
                            name="photo"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-medium text-slate-700 outline-none transition file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-slate-800"
                        >
                    </div>

                    <button class="w-full rounded-2xl bg-indigo-700 px-5 py-4 text-sm font-extrabold text-white transition hover:bg-indigo-800">
                        Enregistrer les informations
                    </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-3xl bg-slate-50 p-5 text-center">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-slate-500">Service actif</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-900">{{ $currentService ?: 'Non renseigne' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 text-center">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-slate-500">Experience</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-900">{{ $profile->experience ?? 0 }} ans</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 sm:col-span-2 xl:col-span-1 text-center">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-slate-500">Conseil</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            Gardez votre profil a jour pour apparaitre plus clairement dans les recherches des clients.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section id="artisan-section-portfolio" class="mt-6 hidden">
        <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-amber-700">Travaux</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Mes réalisations</h3>
                </div>
                <span id="portfolio-count-badge" class="rounded-full bg-amber-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-amber-800">
                    0 travail
                </span>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="space-y-4">
                    <input id="portfolio-title" type="text" placeholder="Titre du travail" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                    <textarea id="portfolio-description" rows="4" placeholder="Description du travail réalisé..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-100"></textarea>
                    <div class="rounded-3xl border border-dashed border-amber-300 bg-amber-50 p-4">
                        <button type="button" id="portfolio-camera-open" class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-slate-800">
                            Ouvrir la caméra
                        </button>
                        <div id="portfolio-photo-preview-wrapper" class="mt-4 hidden">
                            <img id="portfolio-photo-preview" alt="Aperçu de la photo prise" class="max-h-64 w-full rounded-2xl bg-slate-950 object-contain">
                            <p id="portfolio-photo-size" class="mt-2 text-xs font-bold text-emerald-700"></p>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            La photo doit être prise maintenant avec la caméra. Elle sera automatiquement compressée avant l'envoi.
                        </p>
                    </div>
                    <button type="button" id="add-portfolio-item" class="w-full rounded-2xl bg-amber-600 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-amber-700">
                        Ajouter un travail
                    </button>
                </div>

                <div id="portfolio-list" class="space-y-3">
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center">
                        <p class="text-sm font-semibold text-slate-500">Aucun travail ajouté pour le moment.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="portfolio-camera-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4">
        <div class="w-full max-w-3xl overflow-hidden rounded-[28px] bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Prendre une photo du travail</h2>
                <button type="button" id="portfolio-camera-close" class="text-slate-500 transition hover:text-slate-900">Fermer</button>
            </div>
            <div class="grid gap-4 px-5 py-4 sm:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-3">
                    <video id="portfolio-camera-video" autoplay playsinline muted class="min-h-72 w-full rounded-2xl bg-black object-cover"></video>
                </div>
                <div class="space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p id="portfolio-camera-message" class="text-sm text-slate-600">Autorisez la caméra, cadrez votre réalisation puis appuyez sur Capturer.</p>
                    </div>
                    <button type="button" id="portfolio-camera-capture" class="w-full rounded-2xl bg-amber-600 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-amber-700">
                        Capturer
                    </button>
                    <p class="text-xs text-slate-500">La caméra fonctionne sur téléphone et ordinateur avec une connexion HTTPS.</p>
                </div>
            </div>
        </div>
    </div>

    <section id="artisan-section-requests" class="mt-6 hidden">
        <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-violet-700">Reception</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Demandes clients</h3>
                </div>
                <span id="request-count-badge" class="rounded-full bg-violet-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-violet-800">
                    0 demande
                </span>
            </div>

            <div id="requests-list" class="space-y-4">
                <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                    <h4 class="text-lg font-extrabold text-slate-900">Aucune demande pour le moment</h4>
                    <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-500">
                        Les messages envoyes depuis l'espace client apparaitront ici.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="artisan-section-reservations" class="mt-6 hidden">
        <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-emerald-700">Planning</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Reservations clients</h3>
                </div>
                <span id="reservation-count-badge" class="rounded-full bg-emerald-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-emerald-800">
                    0 reservation
                </span>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.02fr_0.98fr]">
                <div class="rounded-[28px] border border-emerald-100 bg-[linear-gradient(180deg,#f0fdf4_0%,#ffffff_100%)] p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-emerald-700">Disponibilites</p>
                            <h4 class="mt-1 text-xl font-extrabold text-slate-900">Choisir les jours indisponibles</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="availability-prev-month" type="button" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                                Prec.
                            </button>
                            <button id="availability-next-month" type="button" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                                Suiv.
                            </button>
                        </div>
                    </div>

                    <p id="availability-month-label" class="mt-4 text-sm font-semibold text-slate-500"></p>

                    <div id="availability-panel" class="mt-4 space-y-4">
                        <div class="rounded-[24px] border border-dashed border-slate-200 bg-white px-6 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-500">Chargement des disponibilites...</p>
                        </div>
                    </div>
                </div>

                <div id="reservations-panel" class="space-y-4">
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <p class="text-sm font-semibold text-slate-500">Chargement des reservations...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="artisan-section-chat" class="mt-6 hidden">
        <div class="rounded-[32px] border border-white/70 bg-white/85 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-indigo-700">Chat</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-slate-900">Discussions avec les clients</h3>
                </div>
                <span id="chat-count-badge" class="rounded-full bg-indigo-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-800">
                    0 chat
                </span>
            </div>

            <div id="chat-panel" class="space-y-4">
                <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-slate-500">Acceptez une demande pour ouvrir un chat avec le client.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<div id="artisan-image-preview" class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/85 px-4 py-6 backdrop-blur-sm">
    <div id="artisan-image-preview-backdrop" class="absolute inset-0"></div>
    <button id="artisan-image-preview-close" type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20">
        Fermer
    </button>
    <figure class="relative z-10 mx-auto flex h-[86vh] max-h-full w-full max-w-5xl items-center justify-center">
        <img id="artisan-image-preview-img" src="" alt="" class="h-full w-full rounded-[28px] object-contain shadow-[0_28px_80px_rgba(15,23,42,0.45)]">
        <figcaption id="artisan-image-preview-caption" class="pointer-events-none absolute inset-0 flex items-center justify-center px-5 text-center text-lg font-extrabold text-white drop-shadow-[0_3px_12px_rgba(15,23,42,0.95)] sm:text-2xl"></figcaption>
    </figure>
</div>

<script>
const storageKey = "artisan_match_requests";
const chatStorageKey = "artisan_match_chats";
const artisanReservationsIndexUrl = @json(route('artisan.reservations.index'));
const artisanReservationStatusBaseUrl = @json(url('/artisan/reservations'));
const artisanAvailabilityIndexUrl = @json(route('artisan.availability.index'));
const artisanAvailabilityUpsertUrl = @json(route('artisan.availability.upsert'));
const notificationsIndexUrl = @json(route('notifications.index'));
const notificationsReadUrl = @json(route('notifications.read'));
const artisanClientRequestStatusUrl = @json(route('artisan.client-requests.status'));
const artisanName = @json(auth()->user()->name);
let activeChatId = "";
let pendingChatImageDataUrl = "";
let pendingChatImageName = "";
let portfolioCameraStream = null;
let portfolioCapturedPhoto = null;
let portfolioCapturedPhotoUrl = "";
let pendingChatVoiceDataUrl = "";
let pendingChatVoiceMimeType = "";
let artisanChatRecording = false;
let artisanChatRecorder = null;
let artisanChatStream = null;
let artisanChatChunks = [];
let artisanChatRecordingTimeout = null;
const artisanAvailabilityState = {
    statuses: {},
    selectedDate: "",
    calendarCursor: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    loading: false,
    saving: false,
};
const artisanNotificationsState = {
    items: [],
    unreadCount: 0,
};
const artisanSectionButtons = Array.from(document.querySelectorAll("[data-artisan-section-button]"));
const artisanSections = {
    profile: document.getElementById("artisan-section-profile"),
    portfolio: document.getElementById("artisan-section-portfolio"),
    requests: document.getElementById("artisan-section-requests"),
    reservations: document.getElementById("artisan-section-reservations"),
    chat: document.getElementById("artisan-section-chat"),
};

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

function getLocalDateValue(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

function isPastDate(dateKey) {
    return dateKey < getLocalDateValue(new Date());
}

async function fetchArtisanJson(url, options, fallbackMessage) {
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
    const payload = await response.json().catch(() => null);

    if (!response.ok) {
        throw new Error(getApiErrorMessage(payload, fallbackMessage));
    }

    return payload;
}

function renderArtisanNotifications() {
    const badge = document.getElementById("artisan-notifications-count-badge");
    const list = document.getElementById("artisan-notifications-list");

    if (!badge || !list) {
        return;
    }

    badge.textContent = String(artisanNotificationsState.unreadCount);
    badge.className = artisanNotificationsState.unreadCount > 0
        ? "rounded-full bg-rose-600 px-2 py-0.5 text-xs font-extrabold text-white"
        : "rounded-full bg-slate-300 px-2 py-0.5 text-xs font-extrabold text-slate-700";

    if (artisanNotificationsState.items.length === 0) {
        list.innerHTML = `
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm font-semibold text-slate-500">
                Aucune notification.
            </div>
        `;
        return;
    }

    list.innerHTML = artisanNotificationsState.items.map((notification) => {
        const data = notification.data || {};
        const unreadClass = notification.readAt ? "border-slate-200 bg-white" : "border-indigo-200 bg-indigo-50";

        return `
            <article class="rounded-2xl border ${unreadClass} p-3">
                <p class="text-sm font-extrabold text-slate-900">${escapeHtml(data.title || "Notification")}</p>
                <p class="mt-1 text-xs leading-5 text-slate-600">${escapeHtml(data.message || "")}</p>
            </article>
        `;
    }).join("");
}

async function loadArtisanNotifications() {
    const payload = await fetchArtisanJson(
        notificationsIndexUrl,
        {
            method: "GET",
        },
        "Impossible de charger les notifications."
    );

    artisanNotificationsState.items = Array.isArray(payload.notifications) ? payload.notifications : [];
    artisanNotificationsState.unreadCount = payload.unreadCount || 0;
    renderArtisanNotifications();
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
    return `${formatReservationDate(dateValue)}${timeValue ? ` a ${timeValue}` : ""}`;
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

function getAvailabilityStatusLabel(dateKey) {
    if (isPastDate(dateKey)) {
        return {
            label: "Jour passe",
            classes: "bg-slate-200 text-slate-600",
        };
    }

    const value = artisanAvailabilityState.statuses[dateKey];

    if (value === false) {
        return {
            label: "Indisponible",
            classes: "bg-rose-100 text-rose-700",
        };
    }

    return {
        label: "Disponible",
        classes: "bg-emerald-100 text-emerald-800",
    };
}

function buildAvailabilityCalendarDays(cursorDate) {
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
        });
    }

    for (let dayNumber = 1; dayNumber <= daysInMonth; dayNumber += 1) {
        const date = new Date(year, month, dayNumber);
        days.push({
            key: getLocalDateValue(date),
            label: dayNumber,
            outsideMonth: false,
        });
    }

    while (days.length % 7 !== 0 || days.length < 35) {
        const nextIndex = days.length - (firstWeekDay + daysInMonth) + 1;
        const date = new Date(year, month + 1, nextIndex);
        days.push({
            key: getLocalDateValue(date),
            label: nextIndex,
            outsideMonth: true,
        });
    }

    return days.map((day) => ({
        ...day,
        status: artisanAvailabilityState.statuses[day.key],
        isPast: isPastDate(day.key),
        isToday: day.key === getLocalDateValue(new Date()),
        isSelected: day.key === artisanAvailabilityState.selectedDate,
    }));
}

function renderAvailabilityManager() {
    const panel = document.getElementById("availability-panel");
    const monthLabel = document.getElementById("availability-month-label");

    if (!panel || !monthLabel) {
        return;
    }

    monthLabel.textContent = artisanAvailabilityState.calendarCursor.toLocaleDateString("fr-FR", {
        month: "long",
        year: "numeric",
    });

    if (!artisanAvailabilityState.selectedDate || isPastDate(artisanAvailabilityState.selectedDate)) {
        artisanAvailabilityState.selectedDate = getLocalDateValue(new Date());
    }

    if (artisanAvailabilityState.loading) {
        panel.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-white px-6 py-10 text-center">
                <p class="text-sm font-semibold text-slate-500">Chargement des disponibilites...</p>
            </div>
        `;
        return;
    }

    const weekDays = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
    const calendarDays = buildAvailabilityCalendarDays(artisanAvailabilityState.calendarCursor);
    const selectedStatus = getAvailabilityStatusLabel(artisanAvailabilityState.selectedDate);
    const monthPrefix = getLocalDateValue(new Date(artisanAvailabilityState.calendarCursor.getFullYear(), artisanAvailabilityState.calendarCursor.getMonth(), 1)).slice(0, 7);
    const monthUnavailableCount = Object.entries(artisanAvailabilityState.statuses).filter(([dateKey, isAvailable]) => dateKey.startsWith(monthPrefix) && isAvailable === false).length;
    const monthVisibleAvailableCount = calendarDays.filter((day) => !day.outsideMonth && !day.isPast && day.status !== false).length;
    const actionsDisabled = isPastDate(artisanAvailabilityState.selectedDate) || artisanAvailabilityState.saving;

    panel.innerHTML = `
        <div class="grid grid-cols-7 gap-2">
            ${weekDays.map((day) => `<div class="px-1 py-2 text-center text-[11px] font-extrabold uppercase tracking-[0.2em] text-slate-400">${day}</div>`).join("")}
            ${calendarDays.map((day) => {
                let classes = "border-slate-200 bg-white text-slate-800 hover:border-emerald-300 hover:text-emerald-700";

                if (day.outsideMonth) {
                    classes = "border-slate-100 bg-slate-50 text-slate-400";
                }

                if (!day.outsideMonth && day.status !== false) {
                    classes = "border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-300";
                } else if (day.status === false) {
                    classes = "border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-300";
                }

                if (day.isPast) {
                    classes = "border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed";
                }

                if (day.isSelected) {
                    classes += " ring-2 ring-slate-900/10";
                } else if (day.isToday && !day.isPast) {
                    classes += " ring-2 ring-sky-200";
                }

                return `
                    <button type="button" data-availability-day="${day.key}" ${day.isPast ? "disabled" : ""} class="min-h-[84px] rounded-2xl border px-2 py-3 text-left text-sm font-bold transition ${classes}">
                        <span class="block">${day.label}</span>
                        <span class="mt-2 inline-flex rounded-full px-2 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] ${day.isPast ? "bg-slate-200 text-slate-600" : (day.status === false ? "bg-rose-100 text-rose-700" : "bg-emerald-100 text-emerald-800")}">
                            ${day.isPast ? "Passe" : (day.status === false ? "Pas dispo" : "Dispo")}
                        </span>
                    </button>
                `;
            }).join("")}
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Jour choisi</p>
                <p class="mt-2 text-sm font-extrabold text-slate-900">${formatReservationDate(artisanAvailabilityState.selectedDate)}</p>
            </div>
            <div class="rounded-3xl bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Dispo par defaut</p>
                <p class="mt-2 text-sm font-extrabold text-emerald-700">${monthVisibleAvailableCount} jour${monthVisibleAvailableCount > 1 ? "s" : ""}</p>
            </div>
            <div class="rounded-3xl bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Pas dispo ce mois</p>
                <p class="mt-2 text-sm font-extrabold text-rose-700">${monthUnavailableCount} jour${monthUnavailableCount > 1 ? "s" : ""}</p>
            </div>
        </div>

        <div class="rounded-3xl border border-white/70 bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Etat du jour</p>
                    <div class="mt-2 flex items-center gap-3">
                        <p class="text-sm font-extrabold text-slate-900">${formatReservationDate(artisanAvailabilityState.selectedDate)}</p>
                        <span class="rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-[0.18em] ${selectedStatus.classes}">${selectedStatus.label}</span>
                    </div>
                </div>
                <p class="text-sm font-semibold text-slate-500">
                    Tous les jours futurs sont disponibles par defaut. Marquez seulement les jours ou vous ne travaillez pas.
                </p>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" id="mark-available-day" ${actionsDisabled ? "disabled" : ""} class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300">
                    Remettre disponible
                </button>
                <button type="button" id="mark-unavailable-day" ${actionsDisabled ? "disabled" : ""} class="rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400">
                    Marquer indisponible
                </button>
            </div>
        </div>
    `;

    panel.querySelectorAll("[data-availability-day]").forEach((button) => {
        button.addEventListener("click", () => {
            artisanAvailabilityState.selectedDate = button.dataset.availabilityDay;
            renderAvailabilityManager();
        });
    });

    const markAvailableButton = document.getElementById("mark-available-day");
    const markUnavailableButton = document.getElementById("mark-unavailable-day");

    if (markAvailableButton) {
        markAvailableButton.addEventListener("click", () => {
            updateAvailability(artisanAvailabilityState.selectedDate, true);
        });
    }

    if (markUnavailableButton) {
        markUnavailableButton.addEventListener("click", () => {
            updateAvailability(artisanAvailabilityState.selectedDate, false);
        });
    }
}

async function loadAvailability() {
    artisanAvailabilityState.loading = true;
    renderAvailabilityManager();

    try {
        const availabilityEntries = await fetchArtisanJson(
            artisanAvailabilityIndexUrl,
            {
                method: "GET",
            },
            "Impossible de charger les disponibilites."
        );

        artisanAvailabilityState.statuses = (Array.isArray(availabilityEntries) ? availabilityEntries : []).reduce((carry, entry) => {
            if (entry && entry.availableDate) {
                carry[entry.availableDate] = Boolean(entry.isAvailable);
            }

            return carry;
        }, {});
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible de charger les disponibilites.", "error");
    } finally {
        artisanAvailabilityState.loading = false;
        renderAvailabilityManager();
    }
}

async function updateAvailability(dateKey, isAvailable) {
    if (!dateKey || isPastDate(dateKey) || artisanAvailabilityState.saving) {
        return;
    }

    artisanAvailabilityState.saving = true;
    renderAvailabilityManager();

    try {
        const response = await fetchArtisanJson(
            artisanAvailabilityUpsertUrl,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    available_date: dateKey,
                    is_available: isAvailable,
                }),
            },
            "Impossible de mettre a jour les disponibilites."
        );

        if (response && response.availability && response.availability.availableDate) {
            if (response.availability.isAvailable) {
                delete artisanAvailabilityState.statuses[response.availability.availableDate];
            } else {
                artisanAvailabilityState.statuses[response.availability.availableDate] = false;
            }
        }

        showArtisanFeedback("Disponibilite mise a jour avec succes.");
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible de mettre a jour les disponibilites.", "error");
    } finally {
        artisanAvailabilityState.saving = false;
        renderAvailabilityManager();
    }
}

function normalizeChatMessage(message) {
    return {
        id: message?.id || ("msg_" + Date.now()),
        sender: message?.sender || "artisan",
        senderName: message?.senderName || "Utilisateur",
        text: String(message?.text || ""),
        imageDataUrl: message?.imageDataUrl || message?.imageUrl || "",
        imageName: message?.imageName || "",
        voiceDataUrl: message?.voiceDataUrl || message?.audioDataUrl || "",
        voiceMimeType: message?.voiceMimeType || message?.audioMimeType || "audio/webm",
        createdAt: message?.createdAt || new Date().toISOString(),
    };
}

function renderChatMessage(message, outgoingSender) {
    const entry = normalizeChatMessage(message);
    const isOutgoing = entry.sender === outgoingSender;
    const wrapperClasses = isOutgoing ? "justify-end" : "justify-start";
    const bubbleClasses = isOutgoing
        ? "rounded-[22px] rounded-br-md bg-[linear-gradient(135deg,#4338ca_0%,#6366f1_100%)] text-white shadow-[0_14px_30px_rgba(79,70,229,0.28)]"
        : "rounded-[22px] rounded-bl-md border border-slate-200 bg-white text-slate-800 shadow-[0_10px_24px_rgba(15,23,42,0.08)]";
    const badgeClasses = isOutgoing
        ? "order-2 bg-indigo-100 text-indigo-800"
        : "bg-slate-200 text-slate-700";
    const timestampClasses = isOutgoing ? "text-indigo-100/80" : "text-slate-400";
    const safeText = escapeHtml(entry.text).replace(/\n/g, "<br>");
    const initial = escapeHtml((entry.senderName || "U").trim().charAt(0).toUpperCase());

    return `
        <div class="flex ${wrapperClasses} gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-extrabold ${badgeClasses}">
                ${initial}
            </div>
            <div class="max-w-[85%] px-4 py-3 text-sm ${bubbleClasses}">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] ${isOutgoing ? "text-indigo-100/90" : "text-slate-400"}">${escapeHtml(entry.senderName)}</p>
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

function clearPendingArtisanChatAttachments() {
    pendingChatImageDataUrl = "";
    pendingChatImageName = "";
    pendingChatVoiceDataUrl = "";
    pendingChatVoiceMimeType = "";
}

function persistArtisanChats(chats) {
    try {
        localStorage.setItem(chatStorageKey, JSON.stringify(chats));
        return true;
    } catch (error) {
        showArtisanFeedback("Impossible d'enregistrer cette piece jointe. Essayez un fichier plus leger.", "error");
        return false;
    }
}

function readBlobAsDataUrl(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result || ""));
        reader.onerror = () => reject(new Error("Impossible de lire le fichier selectionne."));
        reader.readAsDataURL(blob);
    });
}

function dataUrlToBlob(dataUrl) {
    const [header, body] = dataUrl.split(",");
    const mimeMatch = header.match(/data:(.*);base64/);
    const mimeType = mimeMatch ? mimeMatch[1] : "image/jpeg";
    const binary = atob(body);
    const array = new Uint8Array(binary.length);

    for (let i = 0; i < binary.length; i += 1) {
        array[i] = binary.charCodeAt(i);
    }

    return new Blob([array], { type: mimeType });
}

async function resizeImageFileToDataUrl(file, maxDimension = 1280, quality = 0.82) {
    const originalDataUrl = await readBlobAsDataUrl(file);

    return new Promise((resolve, reject) => {
        const image = new Image();

        image.onload = () => {
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
            resolve(canvas.toDataURL(mimeType, quality));
        };

        image.onerror = () => reject(new Error("Impossible de charger l'image."));
        image.src = originalDataUrl;
    });
}

async function resizeImageFile(file, maxDimension = 1600, quality = 0.82) {
    const dataUrl = await resizeImageFileToDataUrl(file, maxDimension, quality);
    return dataUrlToBlob(dataUrl);
}

async function onArtisanChatImageSelected(event) {
    const file = event.target.files && event.target.files[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith("image/")) {
        showArtisanFeedback("Selectionnez une image valide.", "error");
        event.target.value = "";
        return;
    }

    try {
        pendingChatImageDataUrl = await resizeImageFileToDataUrl(file);
        pendingChatImageName = file.name;
        showArtisanFeedback("Photo prete a etre envoyee.");
        loadChats();
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible de preparer cette image.", "error");
    } finally {
        event.target.value = "";
    }
}

function stopArtisanChatStream() {
    if (artisanChatStream) {
        artisanChatStream.getTracks().forEach((track) => track.stop());
    }

    artisanChatStream = null;
}

async function startArtisanVoiceRecording() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder === "undefined") {
        showArtisanFeedback("L'enregistrement vocal n'est pas pris en charge sur cet appareil.", "error");
        return;
    }

    try {
        artisanChatStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        artisanChatChunks = [];
        artisanChatRecorder = new MediaRecorder(artisanChatStream);
        artisanChatRecording = true;
        showArtisanFeedback("Enregistrement vocal en cours...");

        artisanChatRecorder.addEventListener("dataavailable", (event) => {
            if (event.data && event.data.size > 0) {
                artisanChatChunks.push(event.data);
            }
        });

        artisanChatRecorder.addEventListener("stop", async () => {
            const audioBlob = new Blob(artisanChatChunks, {
                type: artisanChatRecorder && artisanChatRecorder.mimeType ? artisanChatRecorder.mimeType : "audio/webm",
            });

            clearTimeout(artisanChatRecordingTimeout);
            artisanChatRecordingTimeout = null;
            stopArtisanChatStream();
            artisanChatRecording = false;

            if (audioBlob.size > 0) {
                try {
                    pendingChatVoiceDataUrl = await readBlobAsDataUrl(audioBlob);
                    pendingChatVoiceMimeType = audioBlob.type || "audio/webm";
                    showArtisanFeedback("Message vocal pret a etre envoye.");
                } catch (error) {
                    showArtisanFeedback(error.message || "Impossible de preparer le message vocal.", "error");
                }
            }

            artisanChatRecorder = null;
            loadChats();
        });

        artisanChatRecorder.start();
        artisanChatRecordingTimeout = window.setTimeout(() => {
            if (artisanChatRecorder && artisanChatRecorder.state === "recording") {
                artisanChatRecorder.stop();
            }
        }, 60000);

        loadChats();
    } catch (error) {
        stopArtisanChatStream();
        artisanChatRecording = false;
        showArtisanFeedback("Impossible d'acceder au microphone.", "error");
    }
}

function toggleArtisanVoiceRecording() {
    if (artisanChatRecorder && artisanChatRecorder.state === "recording") {
        artisanChatRecorder.stop();
        return;
    }

    startArtisanVoiceRecording();
}

function setActiveArtisanSection(sectionName) {
    Object.entries(artisanSections).forEach(([key, section]) => {
        if (!section) {
            return;
        }

        section.classList.toggle("hidden", key !== sectionName);
    });

    artisanSectionButtons.forEach((button) => {
        const isActive = button.dataset.artisanSectionButton === sectionName;
        button.className = isActive
            ? "rounded-2xl bg-slate-900 px-4 py-3 text-left text-white shadow-sm transition"
            : "rounded-2xl px-4 py-3 text-left text-slate-600 transition hover:bg-slate-100 hover:text-slate-900";
    });
}

function loadRequests() {
    const requests = JSON.parse(localStorage.getItem(storageKey) || "[]");
    const list = document.getElementById("requests-list");
    const badge = document.getElementById("request-count-badge");
    const hero = document.getElementById("request-count-hero");
    const pendingRequests = requests.filter((request) =>
        request.artisanName === artisanName && (!request.status || request.status === "nouvelle")
    );

    const label = `${pendingRequests.length} demande${pendingRequests.length > 1 ? 's' : ''}`;
    badge.textContent = label;
    hero.textContent = label;

    if (pendingRequests.length === 0) {
        list.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <h4 class="text-lg font-extrabold text-slate-900">Aucune demande en attente</h4>
                <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-500">
                    Les demandes acceptees ou refusees ne s'affichent plus dans cette liste.
                </p>
            </div>
        `;
        loadChats();
        return;
    }

    list.innerHTML = pendingRequests.map((request) => `
        <article class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-extrabold text-slate-900">${request.clientName}</p>
                    <p class="mt-1 text-sm text-slate-500">${request.serviceType} • ${request.city}</p>
                </div>
                <span class="rounded-full bg-violet-100 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] text-violet-800">
                    ${request.status}
                </span>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Artisan vise</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${request.artisanName}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Budget repere</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${request.price} DZD</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Distance</p>
                    <p class="mt-2 text-sm font-extrabold text-slate-900">${request.distanceKm} km</p>
                </div>
            </div>

            <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                ${request.message}
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" data-action="accept" data-id="${request.id}" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700">
                    Accepter
                </button>
                <button type="button" data-action="refuse" data-id="${request.id}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600">
                    Refuser
                </button>
            </div>
        </article>
    `).join("");

    list.querySelectorAll("[data-action='accept']").forEach((button) => {
        button.addEventListener("click", () => acceptRequest(button.dataset.id));
    });

    list.querySelectorAll("[data-action='refuse']").forEach((button) => {
        button.addEventListener("click", () => refuseRequest(button.dataset.id));
    });

    loadChats();
}

async function loadReservations() {
    const panel = document.getElementById("reservations-panel");
    const badge = document.getElementById("reservation-count-badge");

    if (!panel || !badge) {
        return;
    }

    try {
        const reservations = await fetchArtisanJson(
            artisanReservationsIndexUrl,
            {
                method: "GET",
            },
            "Impossible de charger les reservations."
        );

        const items = Array.isArray(reservations) ? reservations : [];
        badge.textContent = `${items.length} reservation${items.length > 1 ? "s" : ""}`;

        if (items.length === 0) {
            panel.innerHTML = `
                <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                    <h4 class="text-lg font-extrabold text-slate-900">Aucune reservation pour le moment</h4>
                    <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-500">
                        Les rendez-vous crees par vos clients apparaitront ici.
                    </p>
                </div>
            `;
            return;
        }

        panel.innerHTML = items.map((reservation) => {
            const status = getReservationStatusMeta(reservation.status);

            return `
                <article class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h4 class="text-lg font-extrabold text-slate-900">${escapeHtml(reservation.clientName)}</h4>
                                <span class="rounded-full px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] ${status.classes}">
                                    ${status.label}
                                </span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-500">${escapeHtml(reservation.serviceType)} • ${formatReservationDateTime(reservation.reservationDate, reservation.reservationTime)}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[16rem]">
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Ville</p>
                                <p class="mt-2 text-sm font-extrabold text-slate-900">${escapeHtml(reservation.city || "Non renseignee")}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Budget</p>
                                <p class="mt-2 text-sm font-extrabold text-slate-900">${reservation.price !== null && reservation.price !== "" ? `${reservation.price} DZD` : "Non renseigne"}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                        ${escapeHtml(reservation.notes || "Aucun detail complementaire.")}
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        ${reservation.status !== "confirmee" ? `
                            <button type="button" data-reservation-status="confirmee" data-reservation-id="${reservation.id}" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700">
                                Confirmer
                            </button>
                        ` : ""}
                        ${reservation.status !== "annulee" ? `
                            <button type="button" data-reservation-status="annulee" data-reservation-id="${reservation.id}" class="rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                                Annuler
                            </button>
                        ` : ""}
                        ${reservation.status !== "en_attente" ? `
                            <button type="button" data-reservation-status="en_attente" data-reservation-id="${reservation.id}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-300">
                                Remettre en attente
                            </button>
                        ` : ""}
                    </div>
                </article>
            `;
        }).join("");

        panel.querySelectorAll("[data-reservation-status]").forEach((button) => {
            button.addEventListener("click", () => {
                updateReservationStatus(button.dataset.reservationId, button.dataset.reservationStatus);
            });
        });
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible de charger les reservations.", "error");
        panel.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-rose-200 bg-rose-50 px-6 py-10 text-center">
                <p class="text-sm font-semibold text-rose-700">${escapeHtml(error.message || "Impossible de charger les reservations.")}</p>
            </div>
        `;
    }
}

async function updateReservationStatus(reservationId, status) {
    try {
        await fetchArtisanJson(
            `${artisanReservationStatusBaseUrl}/${encodeURIComponent(reservationId)}/status`,
            {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    status: status,
                }),
            },
            "Impossible de mettre a jour la reservation."
        );

        showArtisanFeedback("Reservation mise a jour avec succes.");
        loadArtisanNotifications();
        loadReservations();
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible de mettre a jour la reservation.", "error");
    }
}

async function notifyClientRequestStatus(targetRequest, status) {
    if (!targetRequest.clientUserId) {
        return;
    }

    await fetchArtisanJson(
        artisanClientRequestStatusUrl,
        {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                client_user_id: targetRequest.clientUserId,
                artisan_name: targetRequest.artisanName || artisanName,
                service_type: targetRequest.serviceType || "Service artisan",
                status: status,
                notification_id: targetRequest.notificationId || null,
            }),
        },
        "Impossible d'envoyer la notification au client."
    );
}

async function acceptRequest(id) {
    const requests = JSON.parse(localStorage.getItem(storageKey) || "[]");
    const chats = JSON.parse(localStorage.getItem(chatStorageKey) || "[]");
    const targetRequest = requests.find((request) => request.id === id);

    if (!targetRequest) {
        return;
    }

    try {
        await notifyClientRequestStatus(targetRequest, "acceptee");
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible d'envoyer la notification au client.", "error");
        return;
    }

    let chatId = targetRequest.chatId;
    let updatedChats = [...chats];

    if (!chatId) {
        chatId = `chat_${id}`;
        updatedChats.unshift({
            id: chatId,
            requestId: id,
            artisanName: targetRequest.artisanName,
            clientName: targetRequest.clientName,
            serviceType: targetRequest.serviceType,
            updatedAt: new Date().toISOString(),
            messages: [
                {
                    id: `msg_${Date.now()}`,
                    sender: "client",
                    senderName: targetRequest.clientName,
                    text: targetRequest.message,
                    createdAt: targetRequest.createdAt,
                },
                {
                    id: `msg_${Date.now()}_artisan`,
                    sender: "artisan",
                    senderName: artisanName,
                    text: "Bonjour, votre demande a ete acceptee. Nous pouvons echanger ici.",
                    createdAt: new Date().toISOString(),
                },
            ],
        });
    }

    const updated = requests.map((request) =>
        request.id === id ? { ...request, status: "acceptee", chatId } : request
    );

    localStorage.setItem(storageKey, JSON.stringify(updated));
    localStorage.setItem(chatStorageKey, JSON.stringify(updatedChats));
    activeChatId = chatId;
    loadArtisanNotifications();
    loadRequests();
    setActiveArtisanSection("chat");
}

async function refuseRequest(id) {
    const requests = JSON.parse(localStorage.getItem(storageKey) || "[]");
    const targetRequest = requests.find((request) => request.id === id);

    if (!targetRequest) {
        return;
    }

    try {
        await notifyClientRequestStatus(targetRequest, "refusee");
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible d'envoyer la notification au client.", "error");
        return;
    }

    const updated = requests.map((request) =>
        request.id === id ? { ...request, status: "refusee" } : request
    );
    localStorage.setItem(storageKey, JSON.stringify(updated));

    const chats = JSON.parse(localStorage.getItem(chatStorageKey) || "[]");
    const updatedChats = chats.filter((chat) => chat.requestId !== id);
    localStorage.setItem(chatStorageKey, JSON.stringify(updatedChats));

    if (activeChatId === `chat_${id}`) {
        activeChatId = "";
    }

    loadArtisanNotifications();
    loadRequests();
}

function loadChats() {
    const requests = JSON.parse(localStorage.getItem(storageKey) || "[]");
    const chats = JSON.parse(localStorage.getItem(chatStorageKey) || "[]");
    const panel = document.getElementById("chat-panel");
    const badge = document.getElementById("chat-count-badge");
    const acceptedRequests = requests.filter((request) => request.artisanName === artisanName && request.status === "acceptee" && request.chatId);
    const availableChats = chats.filter((chat) => chat.artisanName === artisanName && acceptedRequests.some((request) => request.chatId === chat.id));

    badge.textContent = `${availableChats.length} chat${availableChats.length > 1 ? 's' : ''}`;

    if (availableChats.length === 0) {
        panel.innerHTML = `
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <p class="text-sm font-semibold text-slate-500">Acceptez une demande pour ouvrir un chat avec le client.</p>
            </div>
        `;
        return;
    }

    if (!activeChatId || !availableChats.some((chat) => chat.id === activeChatId)) {
        activeChatId = availableChats[0].id;
    }

    const currentChat = availableChats.find((chat) => chat.id === activeChatId);

    panel.innerHTML = `
        <div class="flex flex-wrap gap-2 rounded-[26px] border border-white/70 bg-white/85 p-3 shadow-[0_16px_40px_rgba(15,23,42,0.08)]">
            ${availableChats.map((chat) => `
                <button type="button" data-chat-id="${chat.id}" class="chat-switch rounded-full px-4 py-2 text-sm font-bold transition ${chat.id === activeChatId ? 'bg-[linear-gradient(135deg,#0f172a_0%,#334155_100%)] text-white shadow-[0_10px_24px_rgba(15,23,42,0.22)]' : 'border border-slate-200 bg-white text-slate-700 hover:border-indigo-300 hover:text-indigo-700'}">
                    ${chat.clientName}
                </button>
            `).join('')}
        </div>

        <div class="overflow-hidden rounded-[30px] border border-white/70 bg-white/90 shadow-[0_24px_70px_rgba(15,23,42,0.10)] backdrop-blur">
            <div class="border-b border-slate-100 bg-[linear-gradient(135deg,#eef2ff_0%,#f8fafc_100%)] px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,#4338ca_0%,#6366f1_100%)] text-sm font-extrabold text-white shadow-[0_12px_25px_rgba(79,70,229,0.28)]">
                        ${escapeHtml(currentChat.clientName.trim().charAt(0).toUpperCase())}
                    </div>
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">${currentChat.clientName}</p>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">${currentChat.serviceType}</p>
                    </div>
                </div>
            </div>
            <div class="max-h-[28rem] space-y-4 overflow-y-auto bg-[radial-gradient(circle_at_top,#f8fafc_0%,#eef4ff_55%,#f8fafc_100%)] px-4 py-5 sm:px-5">
                ${currentChat.messages.map((message) => renderChatMessage(message, "artisan")).join('')}
            </div>

            <div class="border-t border-slate-100 bg-white px-4 py-4 sm:px-5">
                <div class="space-y-3 rounded-[28px] border border-slate-200 bg-slate-50/80 p-3 shadow-inner">
                <div class="flex flex-wrap gap-2">
                    <input id="artisan-chat-image-input" type="file" accept="image/*" class="hidden">
                    <button type="button" id="artisan-add-photo" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-700">
                        Photo
                    </button>
                    <button type="button" id="artisan-record-voice" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold transition ${artisanChatRecording ? 'border-rose-300 bg-rose-50 text-rose-600' : 'text-slate-700 hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-700'}">
                        ${artisanChatRecording ? 'Arreter le vocal' : 'Vocal'}
                    </button>
                    ${(pendingChatImageDataUrl || pendingChatVoiceDataUrl) ? `
                        <button type="button" id="artisan-clear-attachments" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600">
                            Effacer les pieces jointes
                        </button>
                    ` : ''}
                </div>

                ${artisanChatRecording ? `
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                        Enregistrement vocal en cours... cliquez sur "Arreter le vocal" pour finaliser.
                    </div>
                ` : ''}

                ${(pendingChatImageDataUrl || pendingChatVoiceDataUrl) ? `
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">Pieces jointes en attente</p>
                        ${pendingChatImageDataUrl ? `<img src="${pendingChatImageDataUrl}" alt="${escapeHtml(pendingChatImageName || "Photo")}" class="mt-3 max-h-56 w-full rounded-2xl object-cover">` : ''}
                        ${pendingChatVoiceDataUrl ? `
                            <div class="mt-3">
                                <audio controls class="w-full max-w-sm">
                                    <source src="${pendingChatVoiceDataUrl}" type="${escapeHtml(pendingChatVoiceMimeType || "audio/webm")}">
                                    Votre navigateur ne supporte pas l'audio.
                                </audio>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}

                <div class="flex gap-3">
                    <input id="artisan-chat-input" type="text" placeholder="Aa" class="flex-1 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                    <button type="button" id="artisan-send-message" class="rounded-full bg-[linear-gradient(135deg,#4338ca_0%,#6366f1_100%)] px-5 py-3 text-sm font-extrabold text-white shadow-[0_14px_30px_rgba(79,70,229,0.28)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_36px_rgba(79,70,229,0.32)]">
                        Envoyer
                    </button>
                </div>
                </div>
            </div>
        </div>
    `;

    panel.querySelectorAll(".chat-switch").forEach((button) => {
        button.addEventListener("click", () => {
            activeChatId = button.dataset.chatId;
            loadChats();
        });
    });

    const input = panel.querySelector("#artisan-chat-input");
    const sendButton = panel.querySelector("#artisan-send-message");
    const imageInput = panel.querySelector("#artisan-chat-image-input");
    const photoButton = panel.querySelector("#artisan-add-photo");
    const voiceButton = panel.querySelector("#artisan-record-voice");
    const clearButton = panel.querySelector("#artisan-clear-attachments");

    sendButton.addEventListener("click", sendArtisanMessage);

    input.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            sendArtisanMessage();
        }
    });

    if (photoButton && imageInput) {
        photoButton.addEventListener("click", () => {
            imageInput.click();
        });

        imageInput.addEventListener("change", onArtisanChatImageSelected);
    }

    if (voiceButton) {
        voiceButton.addEventListener("click", toggleArtisanVoiceRecording);
    }

    if (clearButton) {
        clearButton.addEventListener("click", () => {
            clearPendingArtisanChatAttachments();
            loadChats();
        });
    }
}

function sendArtisanMessage() {
    const input = document.getElementById("artisan-chat-input");
    const text = input ? input.value.trim() : "";

    if (!activeChatId || (!text && !pendingChatImageDataUrl && !pendingChatVoiceDataUrl)) {
        return;
    }

    const chats = JSON.parse(localStorage.getItem(chatStorageKey) || "[]");
    const updated = chats.map((chat) => {
        if (chat.id !== activeChatId) {
            return chat;
        }

        return {
            ...chat,
            updatedAt: new Date().toISOString(),
            messages: [
                ...chat.messages,
                {
                    id: `msg_${Date.now()}`,
                    sender: "artisan",
                    senderName: artisanName,
                    text: text,
                    imageDataUrl: pendingChatImageDataUrl,
                    imageName: pendingChatImageName,
                    voiceDataUrl: pendingChatVoiceDataUrl,
                    voiceMimeType: pendingChatVoiceMimeType,
                    createdAt: new Date().toISOString(),
                },
            ],
        };
    });

    if (!persistArtisanChats(updated)) {
        return;
    }

    clearPendingArtisanChatAttachments();
    loadChats();
}

function loadPortfolio() {
    fetch('/artisan/portfolio', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(async (response) => {
            const data = await response.json().catch(() => null);

            if (!response.ok) {
                throw new Error(data?.message || "Impossible de charger les travaux.");
            }

            return data;
        })
        .then(items => {
            const list = document.getElementById("portfolio-list");
            const badge = document.getElementById("portfolio-count-badge");

            badge.textContent = `${items.length} travail${items.length > 1 ? 'x' : ''}`;

            if (items.length === 0) {
                list.innerHTML = `
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center">
                        <p class="text-sm font-semibold text-slate-500">Aucun travail ajouté pour le moment.</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = items.map((item) => `
                <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    ${item.photoUrl ? `
                        <div class="relative mb-4 overflow-hidden rounded-2xl">
                            <img
                                src="${item.photoUrl}"
                                alt="${escapeHtml(item.title || "Realisation")}"
                                class="aspect-[16/9] w-full rounded-2xl object-cover select-none"
                                loading="lazy"
                                draggable="false"
                                oncontextmenu="return false;"
                                data-protected-portfolio-image="true"
                                data-image-preview-src="${item.photoUrl}"
                                data-image-preview-caption="${escapeHtml(item.title || "Realisation")}"
                            >
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-[linear-gradient(180deg,rgba(15,23,42,0.10)_0%,rgba(15,23,42,0.85)_100%)] px-4 py-3">
                                <p class="text-sm font-extrabold uppercase tracking-[0.18em] text-white/95 text-center">Image liee a l'artisan</p>
                            </div>
                        </div>
                    ` : ''}
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-extrabold text-slate-900">${item.title}</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">${item.metier}</p>
                        </div>
                        <button type="button" data-delete-portfolio="${item.id}" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:border-rose-300">
                            Supprimer
                        </button>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-500">${item.description}</p>
                </article>
            `).join('');

            list.querySelectorAll("[data-protected-portfolio-image]").forEach((image) => {
                image.addEventListener("dragstart", (event) => event.preventDefault());
                image.addEventListener("contextmenu", (event) => event.preventDefault());
                image.addEventListener("click", () => openArtisanImagePreview(image.dataset.imagePreviewSrc, image.dataset.imagePreviewCaption));
            });

            list.querySelectorAll("[data-delete-portfolio]").forEach((button) => {
                button.addEventListener("click", () => deletePortfolioItem(button.dataset.deletePortfolio));
            });
        })
        .catch(error => {
            showArtisanFeedback(error.message || "Impossible de charger les travaux.", "error");
            console.error('Error loading portfolio:', error);
        });
}

function showArtisanFeedback(message, type = "success") {
    const feedback = document.getElementById("artisan-feedback");

    if (!feedback) {
        return;
    }

    feedback.textContent = message;
    feedback.className = type === "error"
        ? "mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700"
        : "mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700";
}

function openArtisanImagePreview(src, caption) {
    const modal = document.getElementById("artisan-image-preview");
    const image = document.getElementById("artisan-image-preview-img");
    const captionNode = document.getElementById("artisan-image-preview-caption");

    if (!modal || !image || !captionNode || !src) {
        return;
    }

    image.src = src;
    image.alt = caption || "Apercu image";
    captionNode.textContent = caption || "";
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.classList.add("overflow-hidden");
}

function closeArtisanImagePreview() {
    const modal = document.getElementById("artisan-image-preview");
    const image = document.getElementById("artisan-image-preview-img");
    const captionNode = document.getElementById("artisan-image-preview-caption");

    if (!modal || !image || !captionNode) {
        return;
    }

    modal.classList.add("hidden");
    modal.classList.remove("flex");
    image.src = "";
    image.alt = "";
    captionNode.textContent = "";
    document.body.classList.remove("overflow-hidden");
}

function stopPortfolioCamera() {
    if (portfolioCameraStream) {
        portfolioCameraStream.getTracks().forEach((track) => track.stop());
        portfolioCameraStream = null;
    }

    const video = document.getElementById("portfolio-camera-video");

    if (video) {
        video.srcObject = null;
    }
}

function closePortfolioCamera() {
    const modal = document.getElementById("portfolio-camera-modal");

    stopPortfolioCamera();
    modal?.classList.add("hidden");
    modal?.classList.remove("flex");
    document.body.classList.remove("overflow-hidden");
}

async function compressPortfolioCanvas(canvas) {
    const maxBytes = 1.5 * 1024 * 1024;

    for (const quality of [0.78, 0.68, 0.58, 0.48]) {
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, "image/jpeg", quality));

        if (blob && (blob.size <= maxBytes || quality === 0.48)) {
            return blob;
        }
    }

    return null;
}

async function openPortfolioCamera() {
    const modal = document.getElementById("portfolio-camera-modal");
    const video = document.getElementById("portfolio-camera-video");
    const message = document.getElementById("portfolio-camera-message");

    if (!modal || !video || !message || !navigator.mediaDevices?.getUserMedia) {
        showArtisanFeedback("La caméra n'est pas disponible. Utilisez HTTPS et un navigateur récent.", "error");
        return;
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.classList.add("overflow-hidden");
    message.textContent = "Ouverture de la caméra...";

    try {
        portfolioCameraStream = await navigator.mediaDevices.getUserMedia({
            audio: false,
            video: {
                facingMode: { ideal: "environment" },
                width: { ideal: 1920 },
                height: { ideal: 1080 },
            },
        });
        video.srcObject = portfolioCameraStream;
        await video.play();
        message.textContent = "Cadrez votre réalisation puis appuyez sur Capturer.";
    } catch (error) {
        closePortfolioCamera();
        showArtisanFeedback("Accès caméra refusé ou indisponible. Autorisez la caméra dans les réglages du navigateur.", "error");
    }
}

async function capturePortfolioPhoto() {
    const video = document.getElementById("portfolio-camera-video");

    if (!video || !video.videoWidth || !video.videoHeight) {
        showArtisanFeedback("La caméra n'est pas encore prête.", "error");
        return;
    }

    const maxDimension = 1600;
    const scale = Math.min(1, maxDimension / Math.max(video.videoWidth, video.videoHeight));
    const canvas = document.createElement("canvas");
    canvas.width = Math.max(1, Math.round(video.videoWidth * scale));
    canvas.height = Math.max(1, Math.round(video.videoHeight * scale));

    const context = canvas.getContext("2d");

    if (!context) {
        showArtisanFeedback("Impossible de préparer la photo.", "error");
        return;
    }

    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const blob = await compressPortfolioCanvas(canvas);

    if (!blob) {
        showArtisanFeedback("Impossible de compresser la photo.", "error");
        return;
    }

    portfolioCapturedPhoto = blob;

    if (portfolioCapturedPhotoUrl) {
        URL.revokeObjectURL(portfolioCapturedPhotoUrl);
    }

    portfolioCapturedPhotoUrl = URL.createObjectURL(blob);
    document.getElementById("portfolio-photo-preview").src = portfolioCapturedPhotoUrl;
    document.getElementById("portfolio-photo-preview-wrapper").classList.remove("hidden");
    document.getElementById("portfolio-photo-size").textContent =
        `Photo prête : ${canvas.width} × ${canvas.height}px, ${(blob.size / 1024).toFixed(0)} Ko`;

    closePortfolioCamera();
    showArtisanFeedback("Photo capturée et compressée avec succès.");
}

async function addPortfolioItem() {
    const titleInput = document.getElementById("portfolio-title");
    const descriptionInput = document.getElementById("portfolio-description");
    const title = titleInput.value.trim();
    const description = descriptionInput.value.trim();

    if (!title || !description || !portfolioCapturedPhoto) {
        showArtisanFeedback("Remplissez le titre et la description, puis prenez une photo avec la caméra.", "error");
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);

    formData.append('photo', portfolioCapturedPhoto, `realisation-${Date.now()}.jpg`);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/artisan/portfolio', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(async (response) => {
        const data = await response.json().catch(() => null);

        if (!response.ok) {
            throw new Error(data?.message || "Impossible d'enregistrer le travail.");
        }

        return data;
    })
    .then(data => {
        if (data.success) {
            titleInput.value = "";
            descriptionInput.value = "";
            portfolioCapturedPhoto = null;

            if (portfolioCapturedPhotoUrl) {
                URL.revokeObjectURL(portfolioCapturedPhotoUrl);
                portfolioCapturedPhotoUrl = "";
            }

            document.getElementById("portfolio-photo-preview").removeAttribute("src");
            document.getElementById("portfolio-photo-preview-wrapper").classList.add("hidden");
            document.getElementById("portfolio-photo-size").textContent = "";
            showArtisanFeedback("Travail enregistre avec succes.");
            loadPortfolio();
        }
    })
    .catch(error => {
        showArtisanFeedback(error.message || "Impossible d'enregistrer le travail.", "error");
        console.error('Error saving portfolio:', error);
    });
}

function deletePortfolioItem(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce travail ?')) {
        return;
    }

    fetch(`/artisan/portfolio/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async (response) => {
        const data = await response.json().catch(() => null);

        if (!response.ok) {
            throw new Error(data?.message || "Impossible de supprimer le travail.");
        }

        return data;
    })
    .then(data => {
        if (data.success) {
            showArtisanFeedback("Travail supprime avec succes.");
            loadPortfolio();
        }
    })
    .catch(error => {
        showArtisanFeedback(error.message || "Impossible de supprimer le travail.", "error");
        console.error('Error deleting portfolio:', error);
    });
}

window.addEventListener("storage", (event) => {
    if (event.key === storageKey || event.key === chatStorageKey) {
        loadRequests();
    }
});

artisanSectionButtons.forEach((button) => {
    button.addEventListener("click", () => {
        const sectionName = button.dataset.artisanSectionButton;
        setActiveArtisanSection(sectionName);

        if (sectionName === "reservations") {
            loadAvailability();
            loadReservations();
        }
    });
});

setActiveArtisanSection("profile");
loadArtisanNotifications().catch((error) => {
    showArtisanFeedback(error.message || "Impossible de charger les notifications.", "error");
});

document.getElementById("artisan-notifications-read-button")?.addEventListener("click", async () => {
    try {
        await fetchArtisanJson(notificationsReadUrl, {
            method: "PATCH",
        }, "Impossible de marquer les notifications comme lues.");
        await loadArtisanNotifications();
    } catch (error) {
        showArtisanFeedback(error.message || "Impossible de marquer les notifications comme lues.", "error");
    }
});

document.getElementById("add-portfolio-item").addEventListener("click", addPortfolioItem);
document.getElementById("portfolio-camera-open").addEventListener("click", openPortfolioCamera);
document.getElementById("portfolio-camera-close").addEventListener("click", closePortfolioCamera);
document.getElementById("portfolio-camera-capture").addEventListener("click", capturePortfolioPhoto);
document.getElementById("portfolio-camera-modal").addEventListener("click", (event) => {
    if (event.target.id === "portfolio-camera-modal") {
        closePortfolioCamera();
    }
});
document.getElementById("availability-prev-month").addEventListener("click", () => {
    artisanAvailabilityState.calendarCursor = new Date(artisanAvailabilityState.calendarCursor.getFullYear(), artisanAvailabilityState.calendarCursor.getMonth() - 1, 1);
    renderAvailabilityManager();
});
document.getElementById("availability-next-month").addEventListener("click", () => {
    artisanAvailabilityState.calendarCursor = new Date(artisanAvailabilityState.calendarCursor.getFullYear(), artisanAvailabilityState.calendarCursor.getMonth() + 1, 1);
    renderAvailabilityManager();
});
document.getElementById("artisan-image-preview-close").addEventListener("click", closeArtisanImagePreview);
document.getElementById("artisan-image-preview-backdrop").addEventListener("click", closeArtisanImagePreview);
window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closePortfolioCamera();
        closeArtisanImagePreview();
    }
});
window.addEventListener("beforeunload", stopPortfolioCamera);
artisanAvailabilityState.selectedDate = getLocalDateValue(new Date());
renderAvailabilityManager();
loadPortfolio();
loadRequests();
loadAvailability();
loadReservations();
</script>
</body>
</html>
