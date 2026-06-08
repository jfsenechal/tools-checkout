<!-- Step 1: Scan Worker QR Code -->
<div x-show="!selectedWorker" class="bg-white rounded-lg shadow-lg p-6 mb-6">
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

<!-- Step 2: Worker identified — show info + scan tools in a loop -->
<div x-show="selectedWorker" x-cloak class="space-y-6">
    <!-- Selected Worker Card -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Ouvrier</h2>
            <button @click="finishSession()" class="text-gray-500 hover:text-gray-700" title="Terminer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-lg font-semibold" x-text="selectedWorker?.first_name + ' ' + selectedWorker?.last_name"></p>
        <p class="text-sm text-gray-500" x-text="selectedWorker?.email"></p>
    </div>

    <!-- Scan Tool for Checkout -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Scanner un outil pour le prêt</h3>

        <!-- Camera View -->
        <div x-show="cameraActive" class="relative mb-4 cursor-pointer" @click="confirmScan()">
            <video id="video-tool-wf" class="w-full rounded-lg bg-black" playsinline></video>
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

    <!-- Running list of tools checked out in this session -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Outils prêtés</h3>
            <span
                class="inline-flex items-center justify-center min-w-7 h-7 px-2 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold"
                x-text="sessionTools.length"></span>
        </div>

        <p x-show="sessionTools.length === 0" class="text-gray-500 text-sm text-center py-4">
            Aucun outil prêté pour le moment. Scannez un outil pour commencer.
        </p>

        <ul x-show="sessionTools.length > 0" class="divide-y divide-gray-100">
            <template x-for="(item, index) in sessionTools" :key="index">
                <li class="flex items-center gap-3 py-3">
                    <span class="flex-shrink-0 w-9 h-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate" x-text="item.name"></p>
                        <p class="text-xs text-gray-500" x-text="item.category"></p>
                    </div>
                    <span class="text-xs text-gray-400 flex-shrink-0" x-text="item.time"></span>
                </li>
            </template>
        </ul>
    </div>

    <!-- Finish session -->
    <button
        @click="finishSession()"
        class="w-full bg-gray-800 text-white px-6 py-4 rounded-lg font-semibold hover:bg-gray-900 transition">
        Terminer
    </button>
</div>
