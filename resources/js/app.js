import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import Toastify from 'toastify-js';
import "toastify-js/src/toastify.css";

window.Pusher = Pusher;
window.Alpine = Alpine;

window.App = {
    config: {
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
        baseUrl: window.location.origin,
        debug: true,
        echo: null,
    },


    init() {
        this.bindCommonEvents();
        this.initAlpine();
        // Initialize Echo early for real-time updates
        this.initEcho();
        // Components will be initialized by loader after they're loaded

        if(this.config.debug){
            this.utils.log('App init');
        }
    },

    /**
     * Initialize global components
     * Called after components are loaded by the loader
     */
    initComponents() {
        if (App.components && typeof App.components.Layout !== 'undefined') {
            App.components.Layout.init();
        }
    },


    initAlpine() {
        Alpine.start();
    },

    /**
     * Initialize Echo/Pusher (lazy loaded when needed)
     * Only initialize if not already initialized
     */
    initEcho() {
        if (this.config.echo) {
            return; // Already initialized
        }

        try {
            this.config.echo = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                forceTLS: true,
            });

            // Example subscription
            this.config.echo.channel('test-channel')
                .listen('TestPusherEvent', (e) => {
                    this.utils.showToast(`${e.message}`, 'success');
                    this.utils.log('Pusher event received:', e);
                });
        } catch (error) {
            if (this.config.debug) {
                console.warn('[App] Echo initialization failed:', error);
            }
        }
    },

    bindCommonEvents() {
    },

    utils: {
        log(...args) {
            if (App.config.debug) console.log('[App]', ...args);
        },

        showToast(message, type = 'info') {
            const backgroundColors = {
                success: "linear-gradient(to right, #00b09b, #96c93d)",
                error: "linear-gradient(to right, #e74c3c, #c0392b)",
                warning: "linear-gradient(to right, #f39c12, #f1c40f)",
                info: "linear-gradient(to right, #3498db, #2980b9)",
                primary: "linear-gradient(to right, #2ecc71, #27ae60)",
            };
        
            Toastify({
                text: message,
                duration: 3500,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                style: {
                    background: backgroundColors[type] || backgroundColors.info,
                    borderRadius: "8px",
                    padding: "12px 20px",
                    fontSize: "18px",
                    color: "#fff",
                },
            }).showToast();
        },

        async ajax(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            };
        
            const config = {
                ...defaults,
                ...options,
                headers: { ...defaults.headers, ...(options.headers || {}) },
            };
        
            if (config.method.toUpperCase() !== 'GET') {
                config.headers['X-CSRF-TOKEN'] = App.config.csrfToken;
            }
        
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 10000);
            config.signal = controller.signal;
        
            try {
                const response = await fetch(url, config);
                clearTimeout(timeout);
        
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status} - ${response.statusText}`);
                }
        
                const text = await response.text();
                return text ? JSON.parse(text) : {};
            } catch (err) {
                App.utils.showToast(err.message || 'Network error', 'error');
                throw err;
            }
        },
    },
};