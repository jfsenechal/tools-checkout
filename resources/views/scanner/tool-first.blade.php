<!-- Scanner Section (Tool-First) -->
<div x-show="mode === 'tool-first' && !scannedTool" class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">Scan Tool QR Code</h2>

    <!-- Camera View -->
    <div x-show="cameraActive" class="relative mb-4 cursor-pointer" @click="confirmScan()">
        <video id="video" class="w-full rounded-lg bg-black" playsinline></video>
        <canvas id="canvas" class="hidden"></canvas>
        <div
            class="absolute inset-0 border-4 border-dashed rounded-lg pointer-events-none transition-colors"
            :class="qrDetected ? 'border-green-500' : 'border-blue-500'">
        </div>
        <div
            x-show="qrDetected" x-cloak
            class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-full font-semibold shadow-lg pointer-events-none animate-pulse">
            Tap to confirm
        </div>
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
    <div x-show="scanning && cameraActive && !qrDetected" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-blue-800 text-center">Scanning for QR code...</p>
    </div>
    <div x-show="scanning && cameraActive && qrDetected" x-cloak class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 text-center font-semibold">QR code detected — tap the camera to confirm</p>
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
