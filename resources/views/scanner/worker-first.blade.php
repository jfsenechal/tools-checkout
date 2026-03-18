<!-- Step 1: Scan Worker QR Code -->
<div x-show="mode === 'worker-first' && !selectedWorker" class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">Scanner le code QR de l'ouvrier</h2>

    <!-- Camera View -->
    <div x-show="cameraActive" class="relative mb-4 cursor-pointer" @click="confirmScan()">
        <video id="video-worker" class="w-full rounded-lg bg-black" playsinline></video>
        <canvas id="canvas-worker" class="hidden"></canvas>
        <div
            class="absolute inset-0 border-4 border-dashed rounded-lg pointer-events-none transition-colors"
            :class="qrDetected ? 'border-green-500' : 'border-green-300'">
        </div>
        <div
            x-show="qrDetected" x-cloak
            class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-full font-semibold shadow-lg pointer-events-none animate-pulse">
            Appuyez pour confirmer
        </div>
    </div>

    <div class="flex gap-2">
        <button
            @click="startCamera()"
            x-show="!cameraActive"
            class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Démarrer la caméra
        </button>

        <button
            @click="stopCamera()"
            x-show="cameraActive"
            class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
            Arrêter la caméra
        </button>
    </div>

    <div x-show="scanning && cameraActive && !qrDetected" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 text-center">Scanner le code QR de l'ouvrier...</p>
    </div>
    <div x-show="scanning && cameraActive && qrDetected" x-cloak class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 text-center font-semibold">QR code détecté — Appuyez sur l'écran pour confirmer</p>
    </div>
</div>

<!-- Step 2: Worker identified — show info + scan tool -->
<div x-show="mode === 'worker-first' && selectedWorker" x-cloak class="space-y-6">
    <!-- Selected Worker Card -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Ouvrier</h2>
            <button @click="resetWorkerFirst()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-lg font-semibold" x-text="selectedWorker?.first_name + ' ' + selectedWorker?.last_name"></p>
        <p class="text-sm text-gray-500" x-text="selectedWorker?.email"></p>
    </div>

    <!-- Worker's Active Checkouts -->
    <div x-show="workerCheckouts.length > 0" class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Outils en prêt</h3>
        <div class="space-y-3">
            <template x-for="checkout in workerCheckouts" :key="checkout.id">
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <p class="font-semibold" x-text="checkout.tool.name"></p>
                        <p class="text-sm text-gray-500" x-text="checkout.tool.category || 'N/A'"></p>
                        <p class="text-xs text-gray-400">Depuis : <span x-text="formatDate(checkout.checked_out_at)"></span></p>
                        <span
                            x-show="checkout.is_overdue"
                            class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-800 text-xs font-bold rounded">
                            EN RETARD
                        </span>
                    </div>
                    <button
                        @click="returnToolFromWorker(checkout)"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        Retourner
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Scan Tool for Checkout -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Scanner un outil pour le prêt</h3>

        <!-- Camera View -->
        <div x-show="cameraActive" class="relative mb-4 cursor-pointer" @click="confirmScan()">
            <video id="video-tool-wf" class="w-full rounded-lg bg-black" style="transform: scaleX(-1)" playsinline></video>
            <canvas id="canvas-tool-wf" class="hidden"></canvas>
            <div
                class="absolute inset-0 border-4 border-dashed rounded-lg pointer-events-none transition-colors"
                :class="qrDetected ? 'border-green-500' : 'border-blue-500'">
            </div>
            <div
                x-show="qrDetected" x-cloak
                class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-full font-semibold shadow-lg pointer-events-none animate-pulse">
                Appuyez pour confirmer
            </div>
        </div>

        <div class="flex gap-2">
            <button
                @click="startCamera()"
                x-show="!cameraActive"
                class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Scanner l'outil
            </button>

            <button
                @click="stopCamera()"
                x-show="cameraActive"
                class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                Arrêter la caméra
            </button>
        </div>

        <div x-show="scanning && cameraActive && !qrDetected" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-blue-800 text-center">Recherche du code QR de l'outil...</p>
        </div>
        <div x-show="scanning && cameraActive && qrDetected" x-cloak class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800 text-center font-semibold">Code QR détecté — appuyez sur l'écran pour confirmer</p>
        </div>
    </div>
</div>
