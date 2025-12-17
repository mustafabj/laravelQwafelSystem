import * as utils from './core/utils.js';

const App = {
    config: {
        debug: import.meta.env.DEV === true,
        csrfToken: document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '',
    },
    pages: {},
    components: {},
    services: {},
    state: {},
    utils: {
        ajax: async function(url, options = {}) {
            const defaultOptions = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': App.config.csrfToken,
                },
            };

            const mergedOptions = { ...defaultOptions, ...options };

            try {
                const response = await fetch(url, mergedOptions);
                const data = await response.json();

                if (!response.ok) {
                    // Handle Laravel validation errors (422)
                    if (response.status === 422 && data.errors) {
                        // Display all validation errors
                        const errorMessages = Object.values(data.errors).flat();
                        errorMessages.forEach((msg, index) => {
                            // Stagger toasts slightly so they don't overlap
                            setTimeout(() => {
                                utils.toast(msg, 'error');
                            }, index * 100);
                        });
                    } else if (data.message) {
                        // Display general error message
                        utils.toast(data.message, 'error');
                    } else {
                        // Fallback error message
                        utils.toast(`خطأ: ${response.status}`, 'error');
                    }
                    
                    throw new Error(data.message || `HTTP ${response.status}`);
                }

                return data;
            } catch (error) {
                if (App.config.debug) {
                    console.error('[App.utils.ajax] Error:', error);
                }
                throw error;
            }
        },
        showToast: utils.toast,
        log: utils.log,
        warn: utils.warn,
        error: utils.error,
    },

    init() {
        if (this._initialized) return;
        this._initialized = true;

        if (this.config.debug) {
            console.log('[App] Initialized');
            console.log('[App] CSRF:', this.config.csrfToken);
        }
    },

    initComponents() {
        // Initialize Layout component
        if (this.components.Layout && typeof this.components.Layout.init === 'function') {
            this.components.Layout.init();
            if (this.config.debug) {
                console.log('[App] Layout component initialized');
            }
        }
    }
};

window.App = App;

export default App;
