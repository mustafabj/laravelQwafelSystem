import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';
import App from '../app.js';

/* ================= LOGGER ================= */

export function log(...args) {
    if (App.config.debug) {
        console.log('[App]', ...args);
    }
}

export function warn(...args) {
    if (App.config.debug) {
        console.warn('[App]', ...args);
    }
}

export function error(...args) {
    console.error('[App]', ...args);
}

/* ================= TOAST ================= */

const TOAST_BG = {
    success: 'linear-gradient(to right, #00b09b, #96c93d)',
    error: 'linear-gradient(to right, #e74c3c, #c0392b)',
    warning: 'linear-gradient(to right, #f39c12, #f1c40f)',
    info: 'linear-gradient(to right, #3498db, #2980b9)',
};

export function toast(message, type = 'info') {
    Toastify({
        text: message,
        duration: 3500,
        gravity: 'top',
        position: 'right',
        style: {
            background: TOAST_BG[type] || TOAST_BG.info,
            borderRadius: '8px',
            color: '#fff',
        }
    }).showToast();
}

/* ================= NETWORK ================= */

export async function request(url, { method = 'GET', data = null, timeout = 10000 } = {}) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeout);

    const options = {
        method,
        signal: controller.signal,
        headers: {
            Accept: 'application/json',
        }
    };

    if (data !== null) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(data);
    }

    if (method !== 'GET') {
        options.headers['X-CSRF-TOKEN'] = App.config.csrfToken;
    }

    try {
        const res = await fetch(url, options);
        clearTimeout(timer);

        const text = await res.text();
        const json = text ? JSON.parse(text) : {};

        if (!res.ok) {
            throw new Error(json.message || `HTTP ${res.status}`);
        }

        return json;
    } catch (err) {
        clearTimeout(timer);
        throw err;
    }
}

export const Network = {
    get(url, params = null) {
        let full = url;
        if (params) {
            const qs = new URLSearchParams(params).toString();
            if (qs) full += (url.includes('?') ? '&' : '?') + qs;
        }
        return request(full);
    },
    post(url, data) {
        return request(url, { method: 'POST', data });
    }
};

/* ================= HELPERS ================= */

export function parseIntSafe(value, fallback = 0) {
    const n = parseInt(value, 10);
    return Number.isNaN(n) ? fallback : n;
}

export function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
}

export function debounce(fn, delay = 300) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
}
