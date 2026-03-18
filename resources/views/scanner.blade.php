<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3B82F6">
    <title>Tool Checkout Scanner</title>

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
        #video { transform: scaleX(-1); }
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
                    Tool Scanner
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
                Tool &rarr; Worker
            </button>
            <button
                @click="switchMode('worker-first')"
                class="flex-1 py-3 px-4 rounded-lg font-semibold text-center transition"
                :class="mode === 'worker-first' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'">
                Worker &rarr; Tool
            </button>
        </div>

        <!-- ===================== TOOL-FIRST MODE ===================== -->

        <!-- Scanner Section (Tool-First) -->
        <div x-show="mode === 'tool-first' && !scannedTool" class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Scan Tool QR Code</h2>

            <!-- Camera View -->
            <div x-show="cameraActive" class="relative mb-4">
                <video id="video" class="w-full rounded-lg bg-black" playsinline></video>
                <canvas id="canvas" class="hidden"></canvas>
                <div class="absolute inset-0 border-4 border-blue-500 border-dashed rounded-lg pointer-events-none"></div>
            </div>

            <!-- Camera Controls -->
            <div class="flex gap-2">
                <button
                    @click="startCamera()"
                    x-show="!cameraActive"
                    class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Start Camera
                </button>

                <button
                    @click="stopCamera()"
                    x-show="cameraActive"
                    class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                    Stop Camera
                </button>
            </div>

            <!-- Status Messages -->
            <div x-show="scanning && cameraActive" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-blue-800 text-center">🔍 Scanning for QR code...</p>
            </div>
        </div>

        <!-- Tool Information (Tool-First) -->
        <div x-show="mode === 'tool-first' && scannedTool" x-cloak class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Tool Information</h2>
                <button @click="reset()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-500">Tool Name</span>
                    <p class="text-lg font-semibold" x-text="scannedTool?.name"></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Category</span>
                    <p x-text="scannedTool?.category || 'N/A'"></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Status</span>
                    <span
                        class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                        :class="{
                            'bg-green-100 text-green-800': scannedTool?.status === 'available',
                            'bg-yellow-100 text-yellow-800': scannedTool?.status === 'checked_out',
                            'bg-blue-100 text-blue-800': scannedTool?.status === 'maintenance',
                            'bg-red-100 text-red-800': scannedTool?.status === 'retired'
                        }"
                        x-text="scannedTool?.status?.replace('_', ' ').toUpperCase()">
                    </span>
                </div>

                <!-- Current Checkout Info -->
                <template x-if="currentCheckout">
                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="font-semibold text-yellow-900 mb-2">Currently Checked Out To:</p>
                        <p class="text-yellow-800" x-text="currentCheckout.worker.first_name + ' ' + currentCheckout.worker.last_name"></p>
                        <p class="text-sm text-yellow-600">Since: <span x-text="formatDate(currentCheckout.checked_out_at)"></span></p>
                        <span
                            x-show="currentCheckout.is_overdue"
                            class="inline-block mt-2 px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">
                            OVERDUE
                        </span>
                    </div>
                </template>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 space-y-3">
                <button
                    x-show="scannedTool?.is_available"
                    @click="showWorkerSelection = true"
                    class="w-full bg-green-600 text-white px-6 py-4 rounded-lg font-semibold hover:bg-green-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Checkout Tool
                </button>

                <button
                    x-show="scannedTool?.is_checked_out"
                    @click="returnTool()"
                    class="w-full bg-blue-600 text-white px-6 py-4 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Return Tool
                </button>
            </div>
        </div>

        <!-- ===================== WORKER-FIRST MODE ===================== -->

        <!-- Step 1: Scan Worker QR Code -->
        <div x-show="mode === 'worker-first' && !selectedWorker" class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Scan Worker QR Code</h2>

            <!-- Camera View -->
            <div x-show="cameraActive" class="relative mb-4">
                <video id="video-worker" class="w-full rounded-lg bg-black" style="transform: scaleX(-1)" playsinline></video>
                <canvas id="canvas-worker" class="hidden"></canvas>
                <div class="absolute inset-0 border-4 border-green-500 border-dashed rounded-lg pointer-events-none"></div>
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
                    Start Camera
                </button>

                <button
                    @click="stopCamera()"
                    x-show="cameraActive"
                    class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                    Stop Camera
                </button>
            </div>

            <div x-show="scanning && cameraActive" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-800 text-center">Scanning for worker QR code...</p>
            </div>
        </div>

        <!-- Step 2: Worker identified — show info + scan tool -->
        <div x-show="mode === 'worker-first' && selectedWorker" x-cloak class="space-y-6">
            <!-- Selected Worker Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Worker</h2>
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
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Checked Out Tools</h3>
                <div class="space-y-3">
                    <template x-for="checkout in workerCheckouts" :key="checkout.id">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <p class="font-semibold" x-text="checkout.tool.name"></p>
                                <p class="text-sm text-gray-500" x-text="checkout.tool.category || 'N/A'"></p>
                                <p class="text-xs text-gray-400">Since: <span x-text="formatDate(checkout.checked_out_at)"></span></p>
                                <span
                                    x-show="checkout.is_overdue"
                                    class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-800 text-xs font-bold rounded">
                                    OVERDUE
                                </span>
                            </div>
                            <button
                                @click="returnToolFromWorker(checkout)"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                                Return
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Scan Tool for Checkout -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Scan Tool to Checkout</h3>

                <!-- Camera View -->
                <div x-show="cameraActive" class="relative mb-4">
                    <video id="video-tool-wf" class="w-full rounded-lg bg-black" style="transform: scaleX(-1)" playsinline></video>
                    <canvas id="canvas-tool-wf" class="hidden"></canvas>
                    <div class="absolute inset-0 border-4 border-blue-500 border-dashed rounded-lg pointer-events-none"></div>
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
                        Scan Tool
                    </button>

                    <button
                        @click="stopCamera()"
                        x-show="cameraActive"
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                        Stop Camera
                    </button>
                </div>

                <div x-show="scanning && cameraActive" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-blue-800 text-center">Scanning for tool QR code...</p>
                </div>
            </div>
        </div>

        <!-- Worker Selection Modal (Tool-First) -->
        <div
            x-show="showWorkerSelection"
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click.self="showWorkerSelection = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[80vh] overflow-hidden">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Select Worker</h3>
                        <button @click="showWorkerSelection = false" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <input
                        type="text"
                        x-model="workerSearch"
                        @input="searchWorkers()"
                        placeholder="Search by name or badge number..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="overflow-y-auto max-h-96 p-4">
                    <template x-if="loadingWorkers">
                        <div class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
                        </div>
                    </template>

                    <template x-if="!loadingWorkers && workers.length === 0">
                        <p class="text-center text-gray-500 py-8">No workers found</p>
                    </template>

                    <div class="space-y-2">
                        <template x-for="worker in workers" :key="worker.id">
                            <button
                                @click="selectWorker(worker)"
                                class="w-full text-left p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition">
                                <p class="font-semibold" x-text="worker.first_name + ' ' + worker.last_name"></p>
                                <p class="text-sm text-gray-500" x-text="worker.email"></p>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

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
