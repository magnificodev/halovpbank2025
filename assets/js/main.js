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
        // Check if user is already registered
        if (this.userToken) {
            // Redirect to game page if already registered
            window.location.href = `game.php?token=${this.userToken}`;
            return;
        }

        const form = document.getElementById('registrationForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleRegistration(e));
        }

        // Auto-convert +84 to 0 for phone input
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', (e) => {
                let value = e.target.value;
                if (value.startsWith('+84')) {
                    // Convert +84 to 0
                    value = '0' + value.substring(3);
                    e.target.value = value;
                }
            });
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

        // Hide form and show loading
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        // Hide form inputs
        const inputs = form.querySelectorAll('.form-group');
        inputs.forEach((input) => (input.style.display = 'none'));

        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Đang xử lý...';
        submitBtn.disabled = true;
        submitBtn.style.marginTop = '20px';

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

                // Redirect to game page immediately
                window.location.href = `game.php?token=${this.userToken}`;
            } else {
                // Show error and restore form
                submitBtn.innerHTML =
                    '<span class="error">✗</span> ' + (result.error || 'Có lỗi xảy ra');
                submitBtn.style.background = '#ef4444';
                submitBtn.style.color = 'white';

                // Restore form after 2 seconds
                setTimeout(() => {
                    inputs.forEach((input) => (input.style.display = 'block'));
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    submitBtn.style.background = '';
                    submitBtn.style.color = '';
                    submitBtn.style.marginTop = '';
                }, 2000);
            }
        } catch (error) {
            console.error('Registration error:', error);

            // Show error and restore form
            submitBtn.innerHTML = '<span class="error">✗</span> Có lỗi xảy ra, vui lòng thử lại';
            submitBtn.style.background = '#ef4444';
            submitBtn.style.color = 'white';

            // Restore form after 2 seconds
            setTimeout(() => {
                inputs.forEach((input) => (input.style.display = 'block'));
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.style.background = '';
                submitBtn.style.color = '';
                submitBtn.style.marginTop = '';
            }, 2000);
        }
    }

    validateRegistration(data) {
        let isValid = true;
        let errorMessages = [];

        // Validate full name
        if (!data.full_name.trim()) {
            errorMessages.push('Vui lòng nhập họ tên');
            isValid = false;
        } else if (data.full_name.trim().length < 2) {
            errorMessages.push('Họ tên phải có ít nhất 2 ký tự');
            isValid = false;
        }

        // Validate phone
        if (!data.phone.trim()) {
            errorMessages.push('Vui lòng nhập số điện thoại');
            isValid = false;
        } else if (!data.phone.match(/^(\+84[0-9]{9}|[0-9]{10,11})$/)) {
            errorMessages.push('Số điện thoại phải có 10-11 chữ số');
            isValid = false;
        } else if (!this.isValidVietnamesePhone(data.phone)) {
            errorMessages.push('Số điện thoại không hợp lệ');
            isValid = false;
        }

        // Validate email
        if (!data.email.trim()) {
            errorMessages.push('Vui lòng nhập email');
            isValid = false;
        } else if (!data.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            errorMessages.push('Email không hợp lệ');
            isValid = false;
        }

        // Show alert if there are errors
        if (!isValid && errorMessages.length > 0) {
            alert(errorMessages.join('\n'));
        }

        console.log('Validation result:', isValid);
        return isValid;
    }

    isValidVietnamesePhone(phone) {
        // Remove any spaces or special characters
        let cleanPhone = phone.replace(/[\s\-\(\)]/g, '');

        // Handle international format (+84)
        if (cleanPhone.startsWith('+84')) {
            cleanPhone = '0' + cleanPhone.substring(3);
        }

        // Vietnamese phone number patterns:
        // Mobile: 03x, 05x, 07x, 08x, 09x (10 digits)
        // Landline: 02x (10 digits) or 02xx (11 digits)
        const mobilePattern = /^(03[2-9]|05[6|8|9]|07[0|6|7|8|9]|08[1-6|8|9]|09[0-9])[0-9]{7}$/;
        const landlinePattern = /^(02[0-9])[0-9]{7,8}$/;

        return mobilePattern.test(cleanPhone) || landlinePattern.test(cleanPhone);
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

        // Clear any local dev progress when not in dev mode to avoid stale UI
        if (!this.isDevMode()) {
            localStorage.removeItem('vpbank_station_progress');
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
        const scanBtn = document.getElementById('scanBtn');
        const actionButtons = document.querySelector('.action-image-buttons');
        
        if (!claimBtn || !scanBtn || !actionButtons) return;

        // Check if user has completed at least 3 stations
        const completedStations = result.stations ? result.stations.filter(station => station.completed).length : 0;
        const hasEnoughStations = completedStations >= 3;

        if (result.has_claimed_reward) {
            // User already claimed reward, redirect to reward page
            window.location.href = `reward.php?token=${this.userToken}`;
        } else if (hasEnoughStations && result.can_claim_reward) {
            // Show both buttons
            claimBtn.style.display = 'block';
            scanBtn.style.display = 'block';
            actionButtons.style.justifyContent = 'space-evenly';
        } else {
            // Show only scan button, centered
            claimBtn.style.display = 'none';
            scanBtn.style.display = 'block';
            actionButtons.style.justifyContent = 'center';
        }
    }

    initGameEvents() {
        const scanBtn = document.getElementById('scanQRBtn');
        const claimBtn = document.getElementById('claimRewardBtn');
        // Try to restore local progress to UI
        this.applyLocalProgressToChecklist();

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
        // Dev mode: allow testing without backend verification
        if (this.isDevMode()) {
            this.markStationCompletedLocally(stationId);
            this.applyLocalProgressToChecklist();
            this.showMessage(`Đã đánh dấu hoàn thành ${stationId} (DEV)`, 'success');
            return;
        }
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
                // Mark locally for immediate UI feedback
                this.markStationCompletedLocally(stationId);
                this.applyLocalProgressToChecklist();
                // Also reload from server to stay in sync
                await this.loadGameProgress();
            } else {
                this.showMessage(result.error || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Station completion error:', error);
            this.showMessage('Có lỗi xảy ra khi hoàn thành trạm', 'error');
        }
    }

    isDevMode() {
        // Only enable when explicitly requested via URL: ?dev=1
        try {
            const params = new URLSearchParams(window.location.search);
            return params.get('dev') === '1';
        } catch (_) {
            return false;
        }
    }

    markStationCompletedLocally(stationId) {
        try {
            const key = 'vpbank_station_progress';
            const current = JSON.parse(localStorage.getItem(key) || '{}');
            current[stationId] = true;
            localStorage.setItem(key, JSON.stringify(current));
        } catch (e) {
            console.warn('Local progress save failed', e);
        }
    }

    applyLocalProgressToChecklist() {
        try {
            const key = 'vpbank_station_progress';
            const progress = JSON.parse(localStorage.getItem(key) || '{}');
            const items = document.querySelectorAll('.station-checklist-item');
            items.forEach((li) => {
                const station = li.getAttribute('data-station');
                const icon = li.querySelector('.status-icon');
                if (!station || !icon) return;
                if (progress[station]) {
                    icon.src = 'assets/images/checked-box.png';
                    icon.alt = 'Đã hoàn thành';
                } else {
                    icon.src = 'assets/images/no-checked-box.png';
                    icon.alt = 'Chưa hoàn thành';
                }
            });
            // Update action buttons based on local progress (>= 3 stations)
            const completedCount = Object.values(progress).filter(Boolean).length;
            const mockResult = {
                stations: items.map(item => ({
                    id: item.getAttribute('data-station'),
                    completed: progress[item.getAttribute('data-station')] || false
                })),
                can_claim_reward: completedCount >= 3,
                has_claimed_reward: false
            };
            this.updateActionButtons(mockResult);
        } catch (e) {
            console.warn('Local progress read failed', e);
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
        // Don't show messages on registration page
        if (this.currentPage === 'index') {
            return;
        }

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
