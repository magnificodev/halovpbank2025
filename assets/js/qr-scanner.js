// VPBank Solution Day Game - QR Scanner

class QRScanner {
    constructor() {
        this.scanner = null;
        this.isScanning = false;
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
            const cameras = await Html5Qrcode.getCameras();

            if (cameras.length === 0) {
                this.showScannerError('Không tìm thấy camera');
                return;
            }

            // Use back camera if available, otherwise use first camera
            const cameraId =
                cameras.find(
                    (camera) =>
                        camera.label.toLowerCase().includes('back') ||
                        camera.label.toLowerCase().includes('rear')
                )?.id || cameras[0].id;

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

    async closeScanner() {
        if (!this.isScanning || !this.scanner) return;

        try {
            await this.scanner.stop();
            await this.scanner.clear();
            this.scanner = null;
            this.isScanning = false;

            const modal = document.getElementById('qrScannerModal');
            if (modal) {
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
