// VPBank Solution Day Game - QR Scanner

class QRScanner {
    constructor() {
        this.scanner = null;
        this.isScanning = false;
        this.currentCameraIndex = 0;
        this.availableCameras = [];
        this.init();
    }

    init() {
        // Initialize QR scanner when modal opens
        const modal = document.getElementById('qrScannerModal');
        const closeBtn = modal.querySelector('.close');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeScanner());
        }

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeScanner();
            }
        });

        // Initialize scanner when modal is shown
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    if (modal.style.display === 'block' && !this.isScanning) {
                        this.startScanner();
                    }
                }
            });
        });

        observer.observe(modal, { attributes: true });
    }

    async startScanner() {
        if (this.isScanning) return;

        const qrReader = document.getElementById('qr-reader');
        if (!qrReader) return;

        try {
            // Check if Html5Qrcode is available
            if (typeof Html5Qrcode === 'undefined') {
                console.error('Html5Qrcode library not loaded');
                this.showScannerError('Thư viện quét QR không được tải');
                return;
            }

            this.scanner = new Html5Qrcode('qr-reader');

            // Get available cameras
            this.availableCameras = await Html5Qrcode.getCameras();
            console.log('Available cameras:', this.availableCameras.length, this.availableCameras);

            if (this.availableCameras.length === 0) {
                this.showScannerError('Không tìm thấy camera');
                return;
            }

            // Use back camera if available, otherwise use first camera
            const backCamera = this.availableCameras.find(camera => {
                const label = camera.label.toLowerCase();
                return label.includes('back') || 
                       label.includes('rear') || 
                       label.includes('environment');
            });
            
            const cameraId = backCamera ? backCamera.id : this.availableCameras[0].id;
            this.currentCameraIndex = backCamera ? 
                this.availableCameras.findIndex(c => c.id === backCamera.id) : 0;

            // Start scanning
            await this.scanner.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                },
                (decodedText, decodedResult) => {
                    this.handleQRCode(decodedText);
                },
                (error) => {
                    // Ignore common scanning errors
                    if (error && !error.includes('No QR code found')) {
                        console.log('QR scanning error:', error);
                    }
                }
            );

            this.isScanning = true;
            console.log('QR Scanner started successfully');
        } catch (error) {
            console.error('Failed to start QR scanner:', error);
            this.showScannerError('Không thể khởi động camera');
        }
    }

    handleQRCode(decodedText) {
        console.log('QR Code detected:', decodedText);

        try {
            const url = new URL(decodedText);
            const stationId = url.searchParams.get('station');
            const verifyHash = url.searchParams.get('verify');

            if (stationId && verifyHash) {
                // Close scanner first
                this.closeScanner();

                // Handle station completion
                if (window.vpbankGame) {
                    window.vpbankGame.handleStationCompletion(stationId, verifyHash);
                } else {
                    // Fallback: reload page with station parameters
                    window.location.href = `game.php?station=${stationId}&verify=${verifyHash}&token=${
                        window.vpbankGame?.userToken || ''
                    }`;
                }
            } else {
                this.showScannerError('Mã QR không hợp lệ');
            }
        } catch (error) {
            console.error('Invalid QR code URL:', error);
            this.showScannerError('Mã QR không hợp lệ');
        }
    }

    // Camera switch button removed per latest requirement

    async switchCamera() {
        if (this.availableCameras.length <= 1) {
            console.log('Only one camera available, cannot switch');
            return;
        }

        console.log('Switching camera...');
        
        // Stop current scanner
        if (this.isScanning && this.scanner) {
            try {
                await this.scanner.stop();
                await this.scanner.clear();
            } catch (e) {
                console.log('Error stopping scanner for switch:', e);
            }
        }

        // Switch to next camera
        this.currentCameraIndex = (this.currentCameraIndex + 1) % this.availableCameras.length;
        const newCamera = this.availableCameras[this.currentCameraIndex];
        
        console.log(`Switching to camera ${this.currentCameraIndex}:`, newCamera.label);

        // Restart scanner with new camera
        try {
            await this.scanner.start(
                newCamera.id,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                },
                (decodedText, decodedResult) => {
                    this.handleQRCode(decodedText);
                },
                (error) => {
                    if (error && !error.includes('No QR code found')) {
                        console.log('QR scanning error:', error);
                    }
                }
            );

            this.isScanning = true;
            console.log('Camera switched successfully');
        } catch (error) {
            console.error('Failed to switch camera:', error);
            // Try to restart with original camera
            this.currentCameraIndex = 0;
            this.startScanner();
        }
    }

    async closeScanner() {
        if (!this.isScanning || !this.scanner) return;

        try {
            await this.scanner.stop();
            await this.scanner.clear();
            this.scanner = null;
            this.isScanning = false;

            // Remove camera switch button
            const modal = document.getElementById('qrScannerModal');
            if (modal) {
                const switchButton = modal.querySelector('.camera-switch-btn');
                if (switchButton) {
                    switchButton.remove();
                }
                modal.style.display = 'none';
            }

            console.log('QR Scanner stopped');
        } catch (error) {
            console.error('Error stopping QR scanner:', error);
        }
    }

    showScannerError(message) {
        const qrReader = document.getElementById('qr-reader');
        if (qrReader) {
            qrReader.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #ff4757;">
                    <p>${message}</p>
                    <button onclick="qrScanner.closeScanner()" style="
                        background: #ff4757;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        margin-top: 10px;
                    ">Đóng</button>
                </div>
            `;
        }
    }
}

// Initialize QR Scanner
let qrScanner;
document.addEventListener('DOMContentLoaded', () => {
    qrScanner = new QRScanner();

    // Make it globally accessible
    window.qrScanner = qrScanner;
});
