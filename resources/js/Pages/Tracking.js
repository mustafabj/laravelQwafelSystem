import * as utils from '../core/utils.js';

class Tracking {
    constructor() {
        this.initialized = false;
    }

    init() {
        if (this.initialized) {
            if (App.config.debug) {
                console.log('[Tracking] Already initialized');
            }
            return;
        }

        this.bindEvents();
        this.initialized = true;

        if (App.config.debug) {
            console.log('[Tracking] Initialized');
        }
    }

    bindEvents() {
        // Handle logout form submission
        const logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) {
            logoutForm.addEventListener('submit', (e) => {
                if (!confirm('هل أنت متأكد من تسجيل الخروج؟')) {
                    e.preventDefault();
                }
            });
        }
    }
}

// Register with App.pages
if (typeof App !== 'undefined' && App.pages) {
    App.pages.Tracking = new Tracking();
} else {
    console.error('[Tracking] App.pages not found');
}

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (App && App.pages && App.pages.Tracking) {
            App.pages.Tracking.init();
        }
    });
} else {
    if (App && App.pages && App.pages.Tracking) {
        App.pages.Tracking.init();
    }
}

export default App?.pages?.Tracking || Tracking;

