// VPBank Solution Day Game - Main JavaScript

class VPBankGame {
    constructor() {
        this.userToken = null;
        this.currentPage = this.getCurrentPage();
        this.init();
    }

    init() {
        // Check for user token in localStorage or URL
        this.userToken = this.getUserToken();

        // Initialize based on current page
        switch (this.currentPage) {
            case 'index':
                this.initRegistration();
                break;
            case 'game':
                this.initGame();
                break;
            case 'reward':
                this.initReward();
                break;
        }
    }

    getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('game.php')) return 'game';
        if (path.includes('reward.php')) return 'reward';
        return 'index';
    }

    getUserToken() {
        // Check URL parameter first
        const urlParams = new URLSearchParams(window.location.search);
        const tokenFromUrl = urlParams.get('token');

        if (tokenFromUrl) {
            localStorage.setItem('vpbank_user_token', tokenFromUrl);
            return tokenFromUrl;
        }

        // Check localStorage
        return localStorage.getItem('vpbank_user_token');
    }

    // Registration Page
    initRegistration() {
        const form = document.getElementById('registrationForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleRegistration(e));
        }
    }

    async handleRegistration(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = {
            full_name: formData.get('full_name'),
            phone: formData.get('phone'),
            email: formData.get('email'),
        };

        // Validate form
        if (!this.validateRegistration(data)) {
            return;
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.innerHTML = '<span class="loading"></span> Đang xử lý...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                this.userToken = result.user_token;
                localStorage.setItem('vpbank_user_token', this.userToken);

                // Show success message
                this.showMessage('Đăng ký thành công!', 'success');

                // Redirect to game page
                setTimeout(() => {
                    window.location.href = `game.php?token=${this.userToken}`;
                }, 1500);
            } else {
                this.showMessage(result.error || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showMessage('Có lỗi xảy ra, vui lòng thử lại', 'error');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }

    validateRegistration(data) {
        if (!data.full_name.trim()) {
            this.showMessage('Vui lòng nhập họ tên', 'error');
            return false;
        }

        if (!data.phone.match(/^[0-9]{10,11}$/)) {
            this.showMessage('Số điện thoại phải có 10-11 chữ số', 'error');
            return false;
        }

        if (!data.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            this.showMessage('Email không hợp lệ', 'error');
            return false;
        }

        return true;
    }

    // Game Page
    async initGame() {
        if (!this.userToken) {
            window.location.href = 'index.php';
            return;
        }

        // Check for station completion from URL (external QR scan)
        const urlParams = new URLSearchParams(window.location.search);
        const stationId = urlParams.get('station');
        const verifyHash = urlParams.get('verify');

        if (stationId && verifyHash) {
            await this.handleStationCompletion(stationId, verifyHash);
            // Clean URL and reload
            window.history.replaceState({}, document.title, `game.php?token=${this.userToken}`);
        }

        await this.loadGameProgress();
        this.initGameEvents();
    }

    async loadGameProgress() {
        try {
            const response = await fetch(`api/get-progress.php?token=${this.userToken}`);
            const result = await response.json();

            if (result.success) {
                this.renderStations(result.stations);
                this.updateActionButtons(result);
            } else {
                this.showMessage(result.error || 'Không thể tải tiến độ', 'error');
            }
        } catch (error) {
            console.error('Load progress error:', error);
            this.showMessage('Có lỗi xảy ra khi tải tiến độ', 'error');
        }
    }

    renderStations(stations) {
        const stationsList = document.getElementById('stationsList');
        if (!stationsList) return;

        stationsList.innerHTML = '';

        stations.forEach((station) => {
            const stationElement = document.createElement('div');
            stationElement.className = `station-item ${station.completed ? 'completed' : ''}`;

            stationElement.innerHTML = `
                <div class="station-icon">
                    ${station.completed ? '✓' : '○'}
                </div>
                <div class="station-info">
                    <div class="station-name">${station.name}</div>
                    <div class="station-description">${this.getStationDescription(station.id)}</div>
                </div>
            `;

            stationsList.appendChild(stationElement);
        });
    }

    getStationDescription(stationId) {
        const descriptions = {
            HALLO_GLOW: 'Chụp hình check in',
            HALLO_SOLUTION: 'Thử thách game công nghệ "không chạm"',
            HALLO_SUPER_SINH_LOI: 'Thử thách hứng tiền lời',
            HALLO_SHOP: 'Trải nghiệm các giải pháp thanh toán từ VPBank',
            HALLO_WIN: 'Trò chơi đuổi hình bắt chữ vui nhộn',
        };
        return descriptions[stationId] || '';
    }

    updateActionButtons(result) {
        const claimBtn = document.getElementById('claimRewardBtn');

        if (result.can_claim_reward && !result.has_claimed_reward) {
            claimBtn.style.display = 'block';
        } else if (result.has_claimed_reward) {
            // User already claimed reward, redirect to reward page
            window.location.href = `reward.php?token=${this.userToken}`;
        }
    }

    initGameEvents() {
        const scanBtn = document.getElementById('scanQRBtn');
        const claimBtn = document.getElementById('claimRewardBtn');

        if (scanBtn) {
            scanBtn.addEventListener('click', () => this.openQRScanner());
        }

        if (claimBtn) {
            claimBtn.addEventListener('click', () => this.handleClaimReward());
        }
    }

    openQRScanner() {
        const modal = document.getElementById('qrScannerModal');
        if (modal) {
            modal.style.display = 'block';
            // Initialize QR scanner will be handled by qr-scanner.js
        }
    }

    async handleStationCompletion(stationId, verifyHash) {
        try {
            const response = await fetch('api/check-station.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_token: this.userToken,
                    station_id: stationId,
                    verify_hash: verifyHash,
                }),
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage(result.message, 'success');
                // Reload progress
                await this.loadGameProgress();
            } else {
                this.showMessage(result.error || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Station completion error:', error);
            this.showMessage('Có lỗi xảy ra khi hoàn thành trạm', 'error');
        }
    }

    async handleClaimReward() {
        const claimBtn = document.getElementById('claimRewardBtn');
        const originalText = claimBtn.textContent;

        claimBtn.innerHTML = '<span class="loading"></span> Đang quay...';
        claimBtn.disabled = true;

        try {
            const response = await fetch('api/claim-reward.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_token: this.userToken,
                }),
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage(result.message, 'success');
                // Redirect to reward page
                setTimeout(() => {
                    window.location.href = `reward.php?token=${this.userToken}`;
                }, 2000);
            } else {
                this.showMessage(result.error || 'Có lỗi xảy ra', 'error');
                claimBtn.textContent = originalText;
                claimBtn.disabled = false;
            }
        } catch (error) {
            console.error('Claim reward error:', error);
            this.showMessage('Có lỗi xảy ra khi nhận quà', 'error');
            claimBtn.textContent = originalText;
            claimBtn.disabled = false;
        }
    }

    // Reward Page
    async initReward() {
        if (!this.userToken) {
            window.location.href = 'index.php';
            return;
        }

        await this.loadRewardCode();
    }

    async loadRewardCode() {
        try {
            const response = await fetch(`api/get-progress.php?token=${this.userToken}`);
            const result = await response.json();

            if (result.success) {
                if (result.reward_code) {
                    this.displayPhoneNumber(result.user.phone);
                } else {
                    // User hasn't claimed reward yet, redirect to game
                    window.location.href = `game.php?token=${this.userToken}`;
                }
            } else {
                this.showMessage(result.error || 'Không thể tải thông tin', 'error');
            }
        } catch (error) {
            console.error('Load reward error:', error);
            this.showMessage('Có lỗi xảy ra khi tải thông tin', 'error');
        }
    }

    displayPhoneNumber(phone) {
        const phoneDisplay = document.getElementById('phoneDisplay');
        if (phoneDisplay) {
            phoneDisplay.textContent = phone;
        }
    }

    // Utility Methods
    showMessage(message, type = 'info') {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach((msg) => msg.remove());

        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;

        // Insert message
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(messageDiv, container.firstChild);
        }

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
}

// Initialize the game when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new VPBankGame();
});
