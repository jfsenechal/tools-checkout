function scannerApp() {
    return {
        mode: 'tool-first',
        cameraActive: false,
        scanning: false,
        scannedTool: null,
        currentCheckout: null,
        showWorkerSelection: false,
        selectedWorker: null,
        workers: [],
        workerSearch: '',
        loadingWorkers: false,
        message: { text: '', type: '' },
        video: null,
        canvas: null,
        canvasContext: null,
        scanInterval: null,
        detectedQRData: null,
        qrDetected: false,

        init() {
            this.initCameraElements();

            // Register service worker for offline support
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then(() => console.log('Service worker registered'))
                    .catch(err => console.log('Service worker registration failed:', err));
            }

            // Load workers on init
            this.loadWorkers();
        },

        initCameraElements() {
            if (this.mode === 'worker-first' && this.selectedWorker) {
                this.video = document.getElementById('video-tool-wf');
                this.canvas = document.getElementById('canvas-tool-wf');
            } else if (this.mode === 'worker-first') {
                this.video = document.getElementById('video-worker');
                this.canvas = document.getElementById('canvas-worker');
            } else {
                this.video = document.getElementById('video');
                this.canvas = document.getElementById('canvas');
            }
            if (this.canvas) {
                this.canvasContext = this.canvas.getContext('2d');
            }
        },

        switchMode(newMode) {
            if (this.mode === newMode) return;
            this.stopCamera();
            this.mode = newMode;
            this.scannedTool = null;
            this.currentCheckout = null;
            this.showWorkerSelection = false;
            this.selectedWorker = null;
            this.workerSearch = '';
            this.loadWorkers();
        },

        async startCamera() {
            this.cameraActive = true;
            this.scanning = true;

            // Wait for Alpine to update the DOM (x-show on video elements)
            await this.$nextTick();

            this.initCameraElements();

            if (!this.video) {
                this.showMessage('Élément vidéo introuvable', 'error');
                this.cameraActive = false;
                this.scanning = false;
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                this.video.srcObject = stream;
                await this.video.play();
                this.video.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.startScanning();
            } catch (error) {
                this.cameraActive = false;
                this.scanning = false;
                this.showMessage('Accès à la caméra refusé ou indisponible', 'error');
            }
        },

        stopCamera() {
            if (this.video.srcObject) {
                this.video.srcObject.getTracks().forEach(track => track.stop());
            }
            this.cameraActive = false;
            this.scanning = false;
            this.detectedQRData = null;
            this.qrDetected = false;
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
                this.detectedQRData = code.data;
                this.qrDetected = true;
            } else {
                this.detectedQRData = null;
                this.qrDetected = false;
            }
        },

        confirmScan() {
            if (!this.detectedQRData) return;
            const qrData = this.detectedQRData;
            this.detectedQRData = null;
            this.qrDetected = false;
            this.handleQRCode(qrData);
        },

        async handleQRCode(qrData) {
            this.stopCamera();
            this.scanning = false;

            // Worker-first mode: step 1 — scan worker QR
            if (this.mode === 'worker-first' && !this.selectedWorker) {
                await this.handleWorkerQR(qrData);
                return;
            }

            // Worker-first mode: step 2 — scan tool QR for checkout
            if (this.mode === 'worker-first' && this.selectedWorker) {
                await this.handleToolQRForWorker(qrData);
                return;
            }

            // Tool-first mode: scan tool QR
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
                this.showMessage('Erreur lors du scan du code QR', 'error');
                setTimeout(() => this.startCamera(), 2000);
            }
        },

        async handleWorkerQR(qrData) {
            try {
                const response = await fetch('/api/scanner/scan-worker', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_data: qrData })
                });

                const result = await response.json();

                if (result.success) {
                    this.selectedWorker = result.data.worker;
                    this.showMessage(`Ouvrier : ${result.data.worker.first_name} ${result.data.worker.last_name}`, 'success');
                } else {
                    this.showMessage(result.message, 'error');
                    setTimeout(() => this.startCamera(), 2000);
                }
            } catch (error) {
                this.showMessage('Erreur lors du scan du code QR de l\'ouvrier', 'error');
                setTimeout(() => this.startCamera(), 2000);
            }
        },

        async handleToolQRForWorker(qrData) {
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
                    await this.checkoutToolForWorker(result.data.tool);
                } else {
                    this.showMessage(result.message, 'error');
                    setTimeout(() => this.startCamera(), 2000);
                }
            } catch (error) {
                this.showMessage('Erreur lors du scan du code QR de l\'outil', 'error');
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
                    this.showMessage('Outil prêté avec succès !', 'success');
                    this.showWorkerSelection = false;
                    setTimeout(() => this.reset(), 2000);
                } else {
                    this.showMessage(result.message, 'error');
                }
            } catch (error) {
                this.showMessage('Erreur lors du prêt de l\'outil', 'error');
            }
        },

        async returnTool() {
            if (!confirm('Retourner cet outil ?')) return;

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
                    this.showMessage('Outil retourné avec succès !', 'success');
                    setTimeout(() => this.reset(), 2000);
                } else {
                    this.showMessage(result.message, 'error');
                }
            } catch (error) {
                this.showMessage('Erreur lors du retour de l\'outil', 'error');
            }
        },

        async checkoutToolForWorker(tool) {
            if (!tool.is_available) {
                this.showMessage('L\'outil n\'est pas disponible pour le prêt', 'error');
                setTimeout(() => this.startCamera(), 2000);
                return;
            }

            try {
                const response = await fetch('/api/scanner/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tool_id: tool.id,
                        worker_id: this.selectedWorker.id
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.showMessage('Outil prêté avec succès !', 'success');
                    setTimeout(() => this.startCamera(), 2000);
                } else {
                    this.showMessage(result.message, 'error');
                    setTimeout(() => this.startCamera(), 2000);
                }
            } catch (error) {
                this.showMessage('Erreur lors du prêt de l\'outil', 'error');
                setTimeout(() => this.startCamera(), 2000);
            }
        },

        resetWorkerFirst() {
            this.stopCamera();
            this.selectedWorker = null;
            this.workerSearch = '';
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
