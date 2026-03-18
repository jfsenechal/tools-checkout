<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3B82F6">
    <title>Scanner de prêt d'outils</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="/scanner-manifest.json">
    <link rel="apple-touch-icon" href="/scanner-icon.png">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- jsQR Library for QR scanning -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
       /* #video { transform: scaleX(-1); }*/
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="scannerApp()" x-init="init()">

    <!-- Header -->
    <header class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    Scanner
                </h1>
                <button @click="toggleCamera()" class="p-2 bg-white/20 rounded-lg hover:bg-white/30 transition">
                    <svg x-show="!cameraActive" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <svg x-show="cameraActive" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6 max-w-2xl">

        <!-- Mode Toggle -->
        <div class="bg-white rounded-lg shadow-lg p-2 mb-6 flex">
            <button
                @click="switchMode('tool-first')"
                class="flex-1 py-3 px-4 rounded-lg font-semibold text-center transition"
                :class="mode === 'tool-first' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'">
                Outil &rarr; Ouvrier
            </button>
            <button
                @click="switchMode('worker-first')"
                class="flex-1 py-3 px-4 rounded-lg font-semibold text-center transition"
                :class="mode === 'worker-first' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'">
                Ouvrier &rarr; Outil
            </button>
        </div>

        @include('scanner.tool-first')

        @include('scanner.worker-first')

        <!-- Success/Error Messages -->
        <div
            x-show="message.text"
            x-cloak
            class="fixed bottom-4 right-4 max-w-sm p-4 rounded-lg shadow-lg z-50 animate-slide-up"
            :class="{
                'bg-green-500 text-white': message.type === 'success',
                'bg-red-500 text-white': message.type === 'error',
                'bg-blue-500 text-white': message.type === 'info'
            }">
            <p x-text="message.text"></p>
        </div>

    </main>

    <script src="/js/scanner.js"></script>
</body>
</html>
