<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3B82F6">
    <title>Tool Checkout Scanner</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/scanner/manifest.json">
    <link rel="apple-touch-icon" href="/scanner/icon-192.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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
        
        <!-- Scanner Section -->
        <div x-show="!scannedTool" class="bg-white rounded-lg shadow-lg p-6 mb-6">
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

        <!-- Tool Information -->
        <div x-show="scannedTool" x-cloak class="bg-white rounded-lg shadow-lg p-6 mb-6">
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
                    <span class="text-sm text-gray-500">Tool Code</span>
                    <p class="text-lg font-mono" x-text="scannedTool?.code"></p>
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
                        <p class="text-yellow-800" x-text="currentCheckout.worker.name"></p>
                        <p class="text-sm text-yellow-600">Badge: <span x-text="currentCheckout.worker.badge_number"></span></p>
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

        <!-- Worker Selection Modal -->
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
                                <p class="font-semibold" x-text="worker.name"></p>
                                <p class="text-sm text-gray-600">Badge: <span x-text="worker.badge_number"></span></p>
                                <p class="text-sm text-gray-500" x-text="worker.department"></p>
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

    <script>
        function scannerApp() {
            return {
                cameraActive: false,
                scanning: false,
                scannedTool: null,
                currentCheckout: null,
                showWorkerSelection: false,
                workers: [],
                workerSearch: '',
                loadingWorkers: false,
                message: { text: '', type: '' },
                video: null,
                canvas: null,
                canvasContext: null,
                scanInterval: null,
                
                init() {
                    this.video = document.getElementById('video');
                    this.canvas = document.getElementById('canvas');
                    this.canvasContext = this.canvas.getContext('2d');

                    // Register service worker for offline support
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/sw.js')
                            .then(() => console.log('Service worker registered'))
                            .catch(err => console.log('Service worker registration failed:', err));
                    }

                    // Load workers on init
                    this.loadWorkers();
                },
                
                async startCamera() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: 'environment' } 
                        });
                        this.video.srcObject = stream;
                        await this.video.play();
                        this.cameraActive = true;
                        this.scanning = true;
                        this.startScanning();
                    } catch (error) {
                        this.showMessage('Camera access denied or not available', 'error');
                    }
                },
                
                stopCamera() {
                    if (this.video.srcObject) {
                        this.video.srcObject.getTracks().forEach(track => track.stop());
                    }
                    this.cameraActive = false;
                    this.scanning = false;
                    if (this.scanInterval) {
                        clearInterval(this.scanInterval);
                    }
                },
                
                toggleCamera() {
                    if (this.cameraActive) {
                        this.stopCamera();
                    } else {
                        this.startCamera();
                    }
                },
                
                startScanning() {
                    this.scanInterval = setInterval(() => {
                        this.scan();
                    }, 500);
                },
                
                scan() {
                    if (!this.video.videoWidth || !this.video.videoHeight) return;
                    
                    this.canvas.width = this.video.videoWidth;
                    this.canvas.height = this.video.videoHeight;
                    this.canvasContext.drawImage(this.video, 0, 0);
                    
                    const imageData = this.canvasContext.getImageData(
                        0, 0, this.canvas.width, this.canvas.height
                    );
                    
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    
                    if (code) {
                        this.handleQRCode(code.data);
                    }
                },
                
                async handleQRCode(qrData) {
                    this.stopCamera();
                    this.scanning = false;
                    
                    try {
                        const response = await fetch('/api/scanner/scan', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ qr_data: qrData })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.scannedTool = result.data.tool;
                            this.currentCheckout = result.data.current_checkout;
                        } else {
                            this.showMessage(result.message, 'error');
                            setTimeout(() => this.startCamera(), 2000);
                        }
                    } catch (error) {
                        this.showMessage('Error scanning QR code', 'error');
                        setTimeout(() => this.startCamera(), 2000);
                    }
                },
                
                async loadWorkers() {
                    this.loadingWorkers = true;
                    try {
                        const response = await fetch('/api/scanner/workers');
                        const result = await response.json();
                        if (result.success) {
                            this.workers = result.data;
                        }
                    } catch (error) {
                        console.error('Error loading workers:', error);
                    } finally {
                        this.loadingWorkers = false;
                    }
                },
                
                async searchWorkers() {
                    this.loadingWorkers = true;
                    try {
                        const response = await fetch(`/api/scanner/workers?search=${this.workerSearch}`);
                        const result = await response.json();
                        if (result.success) {
                            this.workers = result.data;
                        }
                    } catch (error) {
                        console.error('Error searching workers:', error);
                    } finally {
                        this.loadingWorkers = false;
                    }
                },
                
                async selectWorker(worker) {
                    try {
                        const response = await fetch('/api/scanner/checkout', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                tool_id: this.scannedTool.id,
                                worker_id: worker.id
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.showMessage('Tool checked out successfully!', 'success');
                            this.showWorkerSelection = false;
                            setTimeout(() => this.reset(), 2000);
                        } else {
                            this.showMessage(result.message, 'error');
                        }
                    } catch (error) {
                        this.showMessage('Error checking out tool', 'error');
                    }
                },
                
                async returnTool() {
                    if (!confirm('Return this tool?')) return;
                    
                    try {
                        const response = await fetch('/api/scanner/return', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                checkout_id: this.currentCheckout.id
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.showMessage('Tool returned successfully!', 'success');
                            setTimeout(() => this.reset(), 2000);
                        } else {
                            this.showMessage(result.message, 'error');
                        }
                    } catch (error) {
                        this.showMessage('Error returning tool', 'error');
                    }
                },
                
                reset() {
                    this.scannedTool = null;
                    this.currentCheckout = null;
                    this.showWorkerSelection = false;
                    this.workerSearch = '';
                    this.startCamera();
                },
                
                showMessage(text, type = 'info') {
                    this.message = { text, type };
                    setTimeout(() => {
                        this.message = { text: '', type: '' };
                    }, 5000);
                },
                
                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleString();
                }
            }
        }
    </script>
</body>
</html>
