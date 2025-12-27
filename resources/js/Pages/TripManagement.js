import * as utils from '../core/utils.js';

class TripManagement {
    constructor() {
        this.initialized = false;
        this.pusher = null;
        this.channel = null;
    }

    init() {
        if (this.initialized) {
            if (App.config.debug) {
                console.log('[TripManagement] Already initialized');
            }
            return;
        }

        this.bindEvents();
        this.initPusher().then(() => {
            // Pusher initialized, continue with countdown
            this.initCountdown();
        }).catch(() => {
            // Pusher failed to load, still initialize countdown
            this.initCountdown();
        });
        this.initialized = true;

        if (App.config.debug) {
            console.log('[TripManagement] Initialized');
        }
    }

    bindEvents() {
        // Driver parcel cards are now standalone, no toggle needed

        // Delay button
        document.querySelectorAll('.delay-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const arrivalId = btn.dataset.arrivalId;
                this.openModal('delayModal' + arrivalId);
            });
        });

        // Mark Arrived button
        document.querySelectorAll('.mark-arrived-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleMarkArrived(e));
        });

        // Edit button
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const arrivalId = btn.dataset.arrivalId;
                const driverParcelId = btn.dataset.driverParcelId;
                const stopPointId = btn.dataset.stopPointId;
                
                // Determine modal ID
                let modalId;
                if (arrivalId) {
                    modalId = 'editModal' + arrivalId;
                } else if (driverParcelId && stopPointId) {
                    modalId = 'editModal' + driverParcelId + '_' + stopPointId;
                } else {
                    return;
                }
                
                this.openModal(modalId);
            });
        });

        // Close modal buttons
        document.querySelectorAll('.modal-close, .btn-secondary[data-modal-id]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modalId = btn.dataset.modalId || btn.closest('.modal')?.id;
                if (modalId) {
                    this.closeModal(modalId);
                }
            });
        });

        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                const modal = overlay.closest('.modal');
                if (modal) {
                    this.closeModal(modal.id);
                }
            });
        });

        // Delay form submit
        document.querySelectorAll('.delay-form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleDelay(e));
        });

        // Edit form submit
        document.querySelectorAll('.edit-form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleEdit(e));
        });
    }

    async initPusher() {
        // Check if Pusher is already loaded
        if (typeof window.Pusher !== 'undefined') {
            this.setupPusher();
            return Promise.resolve();
        }

        // Check if Pusher should be loaded
        const pusherKey = document.querySelector('meta[name="pusher-key"]')?.content;
        const pusherCluster = document.querySelector('meta[name="pusher-cluster"]')?.content;

        if (!pusherKey || !pusherCluster) {
            if (App.config.debug) {
                console.warn('[TripManagement] Pusher configuration not found');
            }
            return Promise.resolve();
        }

        // Load Pusher script dynamically
        try {
            await this.loadPusherScript();
            this.setupPusher();
        } catch (error) {
            if (App.config.debug) {
                console.error('[TripManagement] Failed to load Pusher:', error);
            }
        }
    }

    loadPusherScript() {
        return new Promise((resolve, reject) => {
            // Check if script is already loading or loaded
            const existingScript = document.querySelector('script[src*="pusher.min.js"]');
            if (existingScript) {
                // Script already exists, wait for it to load
                const checkPusher = setInterval(() => {
                    if (typeof window.Pusher !== 'undefined') {
                        clearInterval(checkPusher);
                        resolve();
                    }
                }, 100);

                // Timeout after 10 seconds
                setTimeout(() => {
                    clearInterval(checkPusher);
                    reject(new Error('Pusher script load timeout'));
                }, 10000);
                return;
            }

            // Create and load script
            const script = document.createElement('script');
            script.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
            script.async = true;
            script.onload = () => {
                if (App.config.debug) {
                    console.log('[TripManagement] Pusher script loaded');
                }
                resolve();
            };
            script.onerror = () => {
                reject(new Error('Failed to load Pusher script'));
            };
            document.head.appendChild(script);
        });
    }

    setupPusher() {
        const pusherKey = document.querySelector('meta[name="pusher-key"]')?.content;
        const pusherCluster = document.querySelector('meta[name="pusher-cluster"]')?.content;

        if (!pusherKey || !pusherCluster) {
            if (App.config.debug) {
                console.warn('[TripManagement] Pusher configuration not found');
            }
            return;
        }

        if (typeof window.Pusher === 'undefined') {
            if (App.config.debug) {
                console.warn('[TripManagement] Pusher not available after loading');
            }
            return;
        }

        this.pusher = new window.Pusher(pusherKey, {
            cluster: pusherCluster,
            encrypted: true
        });

        this.channel = this.pusher.subscribe('trip-management');
        
        this.channel.bind('arrival-requested', (data) => {
            if (Notification.permission === 'granted') {
                new Notification('طلب موافقة جديد', {
                    body: `وصل السائق إلى نقطة: ${data.arrival.stopPoint?.stopName || 'نقطة غير محددة'}`,
                    icon: '/image/Logo-qwafel.png'
                });
            }
            setTimeout(() => location.reload(), 2000);
        });

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    initCountdown() {
        this.updateCountdowns();
        setInterval(() => this.updateCountdowns(), 1000);
    }

    updateCountdowns() {
        document.querySelectorAll('.auto-approve-countdown').forEach(element => {
            const requestedAt = parseInt(element.dataset.requestedAt);
            const now = Math.floor(Date.now() / 1000);
            const elapsed = now - requestedAt;
            const remaining = 900 - elapsed; // 15 minutes = 900 seconds

            if (remaining <= 0) {
                element.innerHTML = '<i class="fas fa-exclamation-triangle"></i> تمت الموافقة التلقائية';
                element.classList.add('warning');
                return;
            }

            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            const timerSpan = element.querySelector('.countdown-timer');
            if (timerSpan) {
                timerSpan.textContent = timeString;
            }

            if (remaining < 300) { // Less than 5 minutes
                element.classList.add('warning');
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = ''; // Restore scrolling
        }
    }

    async handleDelay(e) {
        e.preventDefault();
        const form = e.target;
        const arrivalId = form.dataset.arrivalId;
        const formData = new FormData(form);

        // Validate required fields
        const delayReason = formData.get('delayReason');
        const delayDuration = formData.get('delayDuration');

        if (!delayReason || !delayReason.trim()) {
            utils.toast('يرجى إدخال سبب التأخير', 'error');
            return;
        }

        if (!delayDuration || parseInt(delayDuration) < 1) {
            utils.toast('يرجى إدخال مدة التأخير (بالدقائق)', 'error');
            return;
        }

        try {
            const response = await fetch(`/trip-management/arrivals/${arrivalId}/approve-with-delay`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': App.config.csrfToken,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.message) {
                    utils.toast(data.message, 'error');
                } else {
                    utils.toast('حدث خطأ أثناء المعالجة', 'error');
                }
                return;
            }

            if (data.success) {
                utils.toast('تم تسجيل التأخير بنجاح', 'success');
                this.closeModal('delayModal' + arrivalId);
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            if (App.config.debug) {
                console.error('[TripManagement] Delay error:', error);
            }
            utils.toast('حدث خطأ أثناء المعالجة', 'error');
        }
    }

    async handleEdit(e) {
        e.preventDefault();
        const form = e.target;
        const arrivalId = form.dataset.arrivalId;
        const driverParcelId = form.dataset.driverParcelId;
        const stopPointId = form.dataset.stopPointId;
        const formData = new FormData(form);

        // Always include driverParcelId and stopPointId if available (needed for non-arrived points)
        if (driverParcelId && stopPointId) {
            formData.append('driverParcelId', driverParcelId);
            formData.append('stopPointId', stopPointId);
        }

        try {
            // Use route with ID if arrivalId exists and is not empty, otherwise use route without ID
            const url = (arrivalId && arrivalId !== '' && arrivalId !== 'undefined') 
                ? `/trip-management/arrivals/${arrivalId}`
                : '/trip-management/arrivals'; // No ID for new arrivals
            
            const response = await fetch(url, {
                method: 'PUT',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': App.config.csrfToken,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.message) {
                    utils.toast(data.message, 'error');
                } else {
                    utils.toast('حدث خطأ أثناء المعالجة', 'error');
                }
                return;
            }

            if (data.success) {
                utils.toast('تم التحديث بنجاح', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            if (App.config.debug) {
                console.error('[TripManagement] Edit error:', error);
            }
            utils.toast('حدث خطأ أثناء المعالجة', 'error');
        }
    }

    async handleMarkArrived(e) {
        e.preventDefault();
        const btn = e.target.closest('.mark-arrived-btn');
        const driverParcelId = btn.dataset.driverParcelId;
        const stopPointId = btn.dataset.stopPointId;
        const stopName = btn.dataset.stopName;

        if (!confirm(`هل تريد تأكيد وصول السائق إلى نقطة: ${stopName}؟`)) {
            return;
        }

        try {
            const response = await fetch(`/trip-management/driver-parcels/${driverParcelId}/stop-points/${stopPointId}/mark-arrived`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': App.config.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.message) {
                    utils.toast(data.message, 'error');
                } else {
                    utils.toast('حدث خطأ أثناء تسجيل الوصول', 'error');
                }
                return;
            }

            if (data.success) {
                utils.toast('تم تسجيل الوصول بنجاح', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            if (App.config.debug) {
                console.error('[TripManagement] Mark Arrived error:', error);
            }
            utils.toast('حدث خطأ أثناء تسجيل الوصول', 'error');
        }
    }
}

// Export and attach to App
if (typeof App !== 'undefined') {
    if (!App.pages) {
        App.pages = {};
    }
    App.pages.TripManagement = new TripManagement();
    
    // Auto-initialize if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            App.pages.TripManagement.init();
        });
    } else {
        App.pages.TripManagement.init();
    }
}

export default App.pages.TripManagement;

