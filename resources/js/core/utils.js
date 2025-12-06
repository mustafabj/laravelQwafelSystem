import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

/**
 * Toast notification types and their background colors
 */
const TOAST_TYPES = {
    success: 'linear-gradient(to right, #00b09b, #96c93d)',
    error: 'linear-gradient(to right, #e74c3c, #c0392b)',
    warning: 'linear-gradient(to right, #f39c12, #f1c40f)',
    info: 'linear-gradient(to right, #3498db, #2980b9)',
    primary: 'linear-gradient(to right, #2ecc71, #27ae60)',
};

/**
 * Default toast configuration
 */
const DEFAULT_TOAST_CONFIG = {
    duration: 3500,
    gravity: 'top',
    position: 'right',
    stopOnFocus: true,
    style: {
        borderRadius: '8px',
        padding: '12px 20px',
        fontSize: '18px',
        color: '#fff',
    },
};

/**
 * Logger utility
 */
export class Logger {
    constructor(debug = false) {
        this.debug = debug;
    }

    log(...args) {
        if (this.debug) {
            console.log('[App]', ...args);
        }
    }

    warn(...args) {
        if (this.debug) {
            console.warn('[App]', ...args);
        }
    }

    error(...args) {
        console.error('[App]', ...args);
    }
}

/**
 * Toast notification utility
 */
export class Toast {
    show(message, type = 'info') {
        const background = TOAST_TYPES[type] || TOAST_TYPES.info;

        Toastify({
            text: message,
            ...DEFAULT_TOAST_CONFIG,
            style: {
                ...DEFAULT_TOAST_CONFIG.style,
                background,
            },
        }).showToast();
    }

    success(message) {
        this.show(message, 'success');
    }

    error(message) {
        this.show(message, 'error');
    }

    warning(message) {
        this.show(message, 'warning');
    }

    info(message) {
        this.show(message, 'info');
    }
}

/**
 * AJAX utility with CSRF token handling and timeout
 */
export class Ajax {
    constructor(csrfToken) {
        this.csrfToken = csrfToken;
    }

    async request(url, options = {}) {
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
            config.headers['X-CSRF-TOKEN'] = this.csrfToken;
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
            throw err;
        }
    }
}

