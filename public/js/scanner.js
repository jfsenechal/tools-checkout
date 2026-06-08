function scannerApp() {
    return {
        cameraActive: false,
        scanning: false,
        selectedWorker: null,
        sessionTools: [],
        message: { text: '', type: '' },
        video: null,
        canvas: null,
        canvasContext: null,
        scanInterval: null,
        detectedQRData: null,
        qrDetected: false,
        qrLockTimeout: null,
        qrConsecutiveHits: 0,

        init() {
            this.initCameraElements();

            // Register service worker for offline support
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then(() => console.log('Service worker registered'))
                    .catch(err => console.log('Service worker registration failed:', err));
            }
        },

        initCameraElements() {
            if (this.selectedWorker) {
                this.video = document.getElementById('video-tool-wf');
                this.canvas = document.getElementById('canvas-tool-wf');
            } else {
                this.video = document.getElementById('video-worker');
                this.canvas = document.getElementById('canvas-worker');
            }
            if (this.canvas) {
                this.canvasContext = this.canvas.getContext('2d');
            }
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
            if (this.video && this.video.srcObject) {
                this.video.srcObject.getTracks().forEach(track => track.stop());
            }
            this.cameraActive = false;
            this.scanning = false;
            this.detectedQRData = null;
            this.qrDetected = false;
            this.qrConsecutiveHits = 0;
            clearTimeout(this.qrLockTimeout);
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
                this.qrConsecutiveHits++;
                // Require 2 consecutive detections to avoid false positives
                if (this.qrConsecutiveHits >= 2) {
                    this.detectedQRData = code.data;
                    this.qrDetected = true;
                    // Reset the lock timeout — keep detected state stable
                    clearTimeout(this.qrLockTimeout);
                    this.qrLockTimeout = setTimeout(() => {
                        // Only reset if not already confirmed
                        if (this.qrDetected) {
                            this.detectedQRData = null;
                            this.qrDetected = false;
                            this.qrConsecutiveHits = 0;
                        }
                    }, 2000);
                }
            } else {
                // Don't immediately reset — let the lock timeout handle it
                this.qrConsecutiveHits = Math.max(0, this.qrConsecutiveHits - 1);
            }
        },

        confirmScan() {
            if (!this.detectedQRData) return;
            this.playBeep();
            clearTimeout(this.qrLockTimeout);
            const qrData = this.detectedQRData;
            this.detectedQRData = null;
            this.qrDetected = false;
            this.qrConsecutiveHits = 0;
            this.handleQRCode(qrData);
        },

        playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = ctx.createOscillator();
                const gain = ctx.createGain();
                oscillator.connect(gain);
                gain.connect(ctx.destination);
                oscillator.type = 'square';
                oscillator.frequency.setValueAtTime(1800, ctx.currentTime);
                oscillator.frequency.setValueAtTime(1200, ctx.currentTime + 0.08);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.15);
            } catch (e) {
                // Audio not supported, silently ignore
            }
        },

        async handleQRCode(qrData) {
            this.stopCamera();
            this.scanning = false;

            // Step 1 — scan worker QR
            if (!this.selectedWorker) {
                await this.handleWorkerQR(qrData);
                return;
            }

            // Step 2 — scan tool QR for checkout (loops until session is finished)
            await this.handleToolQRForWorker(qrData);
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
                    // Seed the session list with the tools the worker already holds
                    this.sessionTools = (result.data.checkouts || []).map(checkout => ({
                        name: checkout.tool.name,
                        category: checkout.tool.category || '',
                        time: this.formatTime(checkout.checked_out_at),
                    }));
                    this.showMessage(`Ouvrier : ${result.data.worker.first_name} ${result.data.worker.last_name}`, 'success');
                    // Immediately open the camera to start scanning tools
                    this.startCamera();
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
                    this.sessionTools.unshift({
                        name: result.data.tool.name,
                        category: tool.category || '',
                        time: this.formatTime(result.data.checked_out_at),
                    });
                    this.showMessage(`Outil prêté : ${result.data.tool.name}`, 'success');
                    setTimeout(() => this.startCamera(), 1500);
                } else {
                    this.showMessage(result.message, 'error');
                    setTimeout(() => this.startCamera(), 2000);
                }
            } catch (error) {
                this.showMessage('Erreur lors du prêt de l\'outil', 'error');
                setTimeout(() => this.startCamera(), 2000);
            }
        },

        finishSession() {
            this.stopCamera();
            const count = this.sessionTools.length;
            this.selectedWorker = null;
            this.sessionTools = [];
            if (count > 0) {
                this.showMessage(`Session terminée — ${count} outil(s) prêté(s)`, 'info');
            }
        },

        showMessage(text, type = 'info') {
            this.message = { text, type };
            setTimeout(() => {
                this.message = { text: '', type: '' };
            }, 5000);
        },

        formatTime(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    }
}
