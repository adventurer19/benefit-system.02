<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gym Admin Dashboard - Live Check-ins
        </h2>
    </x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .notification-enter {
            transform: translateX(100%);
            opacity: 0;
        }

        .notification-show {
            transform: translateX(0);
            opacity: 1;
            transition: all 0.3s ease-in-out;
        }

        .notification-exit {
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }

        .pulse-green {
            animation: pulse-green 2s infinite;
        }

        .pulse-red {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        }

        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        }

        .table-row-appear {
            animation: slideInLeft 0.5s ease-out;
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .stats-card {
            transition: transform 0.2s ease-in-out;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }
    </style>

    <!-- Notification Area -->
    <div id="notificationArea" class="fixed top-20 right-4 z-50 space-y-2"></div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="stats-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">✓</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Today's Check-ins</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="todayCheckins">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stats-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">✗</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Denied Access</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="todayDenied">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stats-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">👥</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Currently Inside</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="currentlyInside">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stats-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">⚠</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Expiring Soon</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="expiringSoon">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Successful Check-ins -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">✅ Successful Check-ins Today</h2>
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="successCount">0</span>
                        </div>
                    </div>
                    <div class="overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                                </thead>
                                <tbody id="successfulCheckins" class="bg-white divide-y divide-gray-200">
                                <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Denied Access - Requires Action -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">❌ Denied Access - Action Required</h2>
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="deniedCount">0</span>
                        </div>
                    </div>
                    <div class="overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                                </thead>
                                <tbody id="deniedCheckins" class="bg-white divide-y divide-gray-200">
                                <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Log -->
            <div class="mt-8 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">📊 Recent Activity Log</h2>
                </div>
                <div class="overflow-hidden">
                    <div class="max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card UID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            </tr>
                            </thead>
                            <tbody id="activityLog" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced Dashboard with Better Card Reader Detection
        // This fixes the constant request problem

        class GymDashboard {
            constructor() {
                this.lastUpdateTime = null;
                this.isConnected = true;
                this.updateInterval = null;

                // Card reading state management
                this.cardBuffer = '';
                this.cardReadTimeout = null;
                this.lastProcessedCard = null;
                this.lastProcessedTime = 0;
                this.isProcessingCard = false;
                this.cardProcessingTimeout = null;

                // Prevent duplicate detection
                this.recentCards = new Map(); // Store recent cards with timestamps
                this.cardCooldownTime = 5000; // 5 seconds cooldown between same card scans

                this.init();
            }

            init() {
                this.loadInitialData();
                this.startRealTimeUpdates();
                this.bindEvents();
                this.setupCardReaderDetection();
                this.showCardReaderStatus();
            }

            setupCardReaderDetection() {
                console.log('🔍 Setting up card reader detection with improved debouncing...');

                // Method 1: Listen for keystrokes with better filtering
                document.addEventListener('keydown', (event) => {
                    this.handleCardReaderKeyInput(event);
                });

                // Method 2: Create a focused input to catch card reader data
                this.createCardCatcherInput();

                // Method 3: Listen for paste events (some readers paste data)
                document.addEventListener('paste', (event) => {
                    this.handlePasteEvent(event);
                });

                // Clear old card history every minute
                setInterval(() => {
                    this.cleanupOldCards();
                }, 60000);
            }

            createCardCatcherInput() {
                // Create a hidden input that stays focused to catch card reader input
                const cardCatcher = document.createElement('input');
                cardCatcher.type = 'text';
                cardCatcher.id = 'cardCatcher';
                cardCatcher.style.cssText = `
            position: fixed;
            top: -100px;
            left: -100px;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
            z-index: -1;
        `;
                cardCatcher.setAttribute('autocomplete', 'off');
                cardCatcher.setAttribute('tabindex', '-1');

                document.body.appendChild(cardCatcher);

                // Listen for input with proper debouncing
                let inputTimeout;
                cardCatcher.addEventListener('input', (event) => {
                    clearTimeout(inputTimeout);

                    inputTimeout = setTimeout(() => {
                        const value = event.target.value.trim();
                        if (this.isValidCardUID(value)) {
                            this.processCardDetection(value, 'input_catcher');
                        }
                        // Clear the input for next scan
                        event.target.value = '';
                    }, 200); // Wait 200ms after input stops
                });

                // Keep it focused but don't interfere with user interaction
                setInterval(() => {
                    if (document.activeElement !== cardCatcher &&
                        !document.activeElement.matches('input, textarea, select, [contenteditable]')) {
                        cardCatcher.focus();
                    }
                }, 2000);

                cardCatcher.focus();
            }

            handleCardReaderKeyInput(event) {
                // Only process if we're not already processing and not in an input field
                if (this.isProcessingCard || this.isUserTyping()) {
                    return;
                }

                // Card readers typically send only numbers
                if (event.key && /[0-9]/.test(event.key)) {
                    this.cardBuffer += event.key;

                    // Clear previous timeout
                    clearTimeout(this.cardReadTimeout);

                    // Set timeout to process card after input stops
                    this.cardReadTimeout = setTimeout(() => {
                        if (this.cardBuffer.length >= 8 && this.cardBuffer.length <= 20) {
                            this.processCardDetection(this.cardBuffer, 'keyboard_sequence');
                        }
                        this.cardBuffer = '';
                    }, 150); // 150ms delay after last keystroke

                } else if (event.key === 'Enter' && this.cardBuffer.length > 0) {
                    // Many card readers end with Enter
                    clearTimeout(this.cardReadTimeout);
                    this.processCardDetection(this.cardBuffer, 'enter_terminated');
                    this.cardBuffer = '';
                } else if (event.key === 'Escape' || event.key === 'Tab') {
                    // Clear buffer on escape or tab
                    this.cardBuffer = '';
                    clearTimeout(this.cardReadTimeout);
                }
            }

            handlePasteEvent(event) {
                if (this.isUserTyping()) return;

                const pastedData = (event.clipboardData || window.clipboardData).getData('text').trim();
                if (this.isValidCardUID(pastedData)) {
                    event.preventDefault();
                    this.processCardDetection(pastedData, 'paste_event');
                }
            }

            isUserTyping() {
                // Check if user is actively typing in an input field
                const activeElement = document.activeElement;
                return activeElement && (
                    activeElement.matches('input:not(#cardCatcher), textarea, select, [contenteditable]') ||
                    activeElement.id === 'cardCatcher'
                );
            }

            isValidCardUID(value) {
                // Validate card UID format
                return /^\d{8,20}$/.test(value);
            }

            processCardDetection(cardUid, detectionMethod) {
                // Prevent processing if already processing
                if (this.isProcessingCard) {
                    console.log('🔄 Ignoring card scan - already processing');
                    return;
                }

                const now = Date.now();

                // Check if this card was recently processed
                if (this.recentCards.has(cardUid)) {
                    const lastTime = this.recentCards.get(cardUid);
                    if ((now - lastTime) < this.cardCooldownTime) {
                        console.log(`🔄 Ignoring duplicate card scan: ${cardUid} (cooldown: ${Math.round((this.cardCooldownTime - (now - lastTime)) / 1000)}s)`);
                        return;
                    }
                }

                // Set processing flag
                this.isProcessingCard = true;

                // Clear any existing processing timeout
                clearTimeout(this.cardProcessingTimeout);

                // Set timeout to reset processing flag
                this.cardProcessingTimeout = setTimeout(() => {
                    this.isProcessingCard = false;
                }, 3000); // 3 seconds max processing time

                // Update recent cards
                this.recentCards.set(cardUid, now);

                console.log(`🎯 Processing card: ${cardUid} (method: ${detectionMethod})`);

                // Show visual feedback
                this.showCardDetectedFeedback(cardUid);

                // Process the card
                this.validateCardWithAPI(cardUid);
            }

            cleanupOldCards() {
                const now = Date.now();
                const cutoffTime = now - (this.cardCooldownTime * 2); // Keep history for 2x cooldown time

                for (const [cardUid, timestamp] of this.recentCards.entries()) {
                    if (timestamp < cutoffTime) {
                        this.recentCards.delete(cardUid);
                    }
                }

                console.log(`🧹 Cleaned up old card history. Current size: ${this.recentCards.size}`);
            }

            showCardDetectedFeedback(cardUid) {
                // Remove any existing feedback
                const existingFeedback = document.getElementById('cardDetectedFeedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }

                // Create visual feedback
                const feedback = document.createElement('div');
                feedback.id = 'cardDetectedFeedback';
                feedback.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                feedback.innerHTML = `
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-3"></div>
                <span class="font-medium">📇 Processing Card: ${cardUid}</span>
            </div>
        `;

                document.body.appendChild(feedback);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (feedback.parentNode) {
                        feedback.parentNode.removeChild(feedback);
                    }
                }, 5000);
            }

            async validateCardWithAPI(cardUid) {
                try {
                    console.log(`🔍 Validating card: ${cardUid}`);

                    // Get CSRF token from meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    const response = await fetch('/api/gym/validate-card', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            card_uid: cardUid,
                            device_id: 'admin_dashboard'
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const result = await response.json();
                    console.log('✅ API Response:', result);

                    // Show result notification
                    this.showCardValidationResult(result);

                    // Refresh dashboard data to show the new entry
                    setTimeout(() => {
                        this.loadInitialData();
                    }, 1000);

                } catch (error) {
                    console.error('❌ API Error:', error);
                    this.showCardValidationResult({
                        status: 'error',
                        message: `System error: ${error.message}`,
                        card_uid: cardUid
                    });
                } finally {
                    // Reset processing flag
                    this.isProcessingCard = false;
                    clearTimeout(this.cardProcessingTimeout);

                    // Remove processing feedback
                    const feedback = document.getElementById('cardDetectedFeedback');
                    if (feedback) {
                        feedback.remove();
                    }
                }
            }

            showCardValidationResult(result) {
                // Remove any existing result notifications
                const existingNotifications = document.querySelectorAll('.card-validation-result');
                existingNotifications.forEach(notification => notification.remove());

                const isSuccess = result.status === 'success';
                const bgColor = isSuccess ? 'bg-green-500' : result.status === 'error' ? 'bg-red-500' : 'bg-yellow-500';
                const icon = isSuccess ? '✅' : result.status === 'error' ? '❌' : '⚠️';

                const notification = document.createElement('div');
                notification.className = `card-validation-result fixed top-20 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-sm transform transition-all duration-300`;

                notification.innerHTML = `
            <div class="flex items-start">
                <span class="text-2xl mr-3">${icon}</span>
                <div>
                    <div class="font-bold">${result.message}</div>
                    ${result.member ? `
                        <div class="text-sm mt-1">
                            Member: ${result.member.name}<br>
                            Card: ${result.card_uid || 'N/A'}
                        </div>
                    ` : ''}
                    ${result.membership ? `
                        <div class="text-sm mt-1">
                            Days remaining: ${result.membership.days_remaining}
                        </div>
                    ` : ''}
                    <div class="text-xs mt-2 opacity-75">
                        ${new Date().toLocaleTimeString()}
                    </div>
                </div>
            </div>
        `;

                document.body.appendChild(notification);

                // Animate in
                setTimeout(() => {
                    notification.style.transform = 'translateX(0)';
                }, 10);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 5000);

                // Play sound
                this.playValidationSound(isSuccess);
            }

            playValidationSound(isSuccess) {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.setValueAtTime(isSuccess ? 800 : 400, audioContext.currentTime);
                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.2);
                } catch (error) {
                    // Sound not available, ignore
                }
            }

            showCardReaderStatus() {
                const statusIndicator = document.createElement('div');
                statusIndicator.id = 'cardReaderStatus';
                statusIndicator.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg text-sm';
                statusIndicator.innerHTML = `
            <div class="flex items-center">
                <div class="w-2 h-2 bg-white rounded-full animate-pulse mr-2"></div>
                <span>Card Reader: Active</span>
            </div>
        `;

                document.body.appendChild(statusIndicator);
            }

            bindEvents() {
                // Add any additional event bindings here
            }

            // Keep all your existing methods for loading data
            async loadInitialData() {
                try {
                    await this.loadTodayStats();
                    await this.loadSuccessfulCheckins();
                    await this.loadDeniedAccess();
                    await this.loadActivityLog();
                } catch (error) {
                    console.error('Error loading initial data:', error);
                }
            }

            async loadTodayStats() {
                try {
                    const response = await fetch('/admin/api/stats');
                    const stats = await response.json();
                    document.getElementById('todayCheckins').textContent = stats.todayCheckins || 0;
                    document.getElementById('todayDenied').textContent = stats.todayDenied || 0;
                    document.getElementById('currentlyInside').textContent = stats.currentlyInside || 0;
                    document.getElementById('expiringSoon').textContent = stats.expiringSoon || 0;
                } catch (error) {
                    console.error('Error loading stats:', error);
                }
            }

            async loadSuccessfulCheckins() {
                try {
                    const response = await fetch('/admin/api/successful-checkins');
                    const checkins = await response.json();
                    const tbody = document.getElementById('successfulCheckins');
                    tbody.innerHTML = checkins.map(checkin => `
                <tr class="table-row-appear hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${checkin.time}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${checkin.member}</div>
                        <div class="text-sm text-gray-500">${checkin.membership_type}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✓ ${checkin.days_remaining} days left
                        </span>
                    </td>
                </tr>
            `).join('');
                    document.getElementById('successCount').textContent = checkins.length;
                } catch (error) {
                    console.error('Error loading successful checkins:', error);
                }
            }

            async loadDeniedAccess() {
                try {
                    const response = await fetch('/admin/api/denied-access');
                    const denied = await response.json();
                    const tbody = document.getElementById('deniedCheckins');
                    tbody.innerHTML = denied.map(entry => `
                <tr class="table-row-appear hover:bg-red-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.time}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${entry.member}</div>
                        <div class="text-sm text-gray-500">Card: ${entry.card_uid}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ${entry.reason}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="dashboard.handleRenewal(${entry.member_id}, '${entry.member}')"
                                class="text-blue-600 hover:text-blue-900 mr-2">Renew</button>
                        <button onclick="dashboard.contactMember(${entry.member_id}, '${entry.member}')"
                                class="text-green-600 hover:text-green-900">Contact</button>
                    </td>
                </tr>
            `).join('');
                    document.getElementById('deniedCount').textContent = denied.length;
                } catch (error) {
                    console.error('Error loading denied access:', error);
                }
            }

            async loadActivityLog() {
                try {
                    const response = await fetch('/admin/api/activity-log');
                    const activities = await response.json();
                    const tbody = document.getElementById('activityLog');
                    tbody.innerHTML = activities.map(activity => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${activity.time}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${activity.member}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${activity.card_uid}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        activity.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }">
                            ${activity.status === 'success' ? '✓' : '✗'} ${activity.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${activity.reason}</td>
                </tr>
            `).join('');
                } catch (error) {
                    console.error('Error loading activity log:', error);
                }
            }

            startRealTimeUpdates() {
                this.updateInterval = setInterval(() => {
                    this.fetchRealTimeUpdates();
                }, 5000);
            }

            async fetchRealTimeUpdates() {
                try {
                    const since = this.lastUpdateTime || new Date(Date.now() - 5 * 60 * 1000).toISOString();
                    const response = await fetch(`/admin/api/updates?since=${since}`);
                    const data = await response.json();
                    if (data.updates && data.updates.length > 0) {
                        data.updates.forEach(update => this.processRealTimeUpdate(update));
                    }
                    this.lastUpdateTime = data.last_update;
                } catch (error) {
                    console.error('Error fetching real-time updates:', error);
                }
            }

            processRealTimeUpdate(update) {
                const isSuccess = update.status === 'success';
                if (isSuccess) {
                    this.addSuccessfulCheckin(update);
                } else {
                    this.addDeniedAccess(update);
                }
                this.incrementStat(isSuccess ? 'todayCheckins' : 'todayDenied');
            }

            addSuccessfulCheckin(checkin) {
                const tbody = document.getElementById('successfulCheckins');
                const newRow = document.createElement('tr');
                newRow.className = 'table-row-appear hover:bg-gray-50 bg-green-50';
                newRow.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${checkin.time}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${checkin.member}</div>
                <div class="text-sm text-gray-500">${checkin.membership_type}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ✓ ${checkin.days_remaining} days left
                </span>
            </td>
        `;
                tbody.insertBefore(newRow, tbody.firstChild);
                this.incrementCounter('successCount');
                setTimeout(() => newRow.classList.remove('bg-green-50'), 3000);
            }

            addDeniedAccess(entry) {
                const tbody = document.getElementById('deniedCheckins');
                const newRow = document.createElement('tr');
                newRow.className = 'table-row-appear hover:bg-red-50 bg-red-50';
                newRow.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${entry.time}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${entry.member}</div>
                <div class="text-sm text-gray-500">Card: ${entry.card_uid}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    ${entry.reason}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="dashboard.handleRenewal(${entry.member_id || 1}, '${entry.member}')"
                        class="text-blue-600 hover:text-blue-900 mr-2">Renew</button>
                <button onclick="dashboard.contactMember(${entry.member_id || 1}, '${entry.member}')"
                        class="text-green-600 hover:text-green-900">Contact</button>
            </td>
        `;
                tbody.insertBefore(newRow, tbody.firstChild);
                this.incrementCounter('deniedCount');
            }

            incrementStat(statId) {
                const element = document.getElementById(statId);
                element.textContent = parseInt(element.textContent) + 1;
            }

            incrementCounter(counterId) {
                const counter = document.getElementById(counterId);
                counter.textContent = parseInt(counter.textContent) + 1;
            }

            async handleRenewal(memberId, memberName) {
                alert(`Renewal functionality for ${memberName} (ID: ${memberId})`);
            }

            async contactMember(memberId, memberName) {
                alert(`Contact functionality for ${memberName} (ID: ${memberId})`);
            }
        }

        // Initialize dashboard when page loads
        let dashboard;
        document.addEventListener('DOMContentLoaded', function() {
            dashboard = new GymDashboard();
            console.log('🏋️ Gym Dashboard initialized with improved card reader detection');
        });
    </script>
</x-app-layout>
