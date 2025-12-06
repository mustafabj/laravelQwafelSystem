/**
 * Application configuration
 */
export class AppConfig {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        this.baseUrl = window.location.origin;
        this.debug = true;
        this._echo = null;
        this.loader = null;
    }

    /**
     * Get Echo instance
     * @returns {Echo|null}
     */
    getEcho() {
        return this._echo;
    }

    /**
     * Set Echo instance
     * @param {Echo|null} echoInstance
     */
    setEcho(echoInstance) {
        this._echo = echoInstance;
    }

    /**
     * Get CSRF token
     * @returns {string}
     */
    getCsrfToken() {
        return this.csrfToken;
    }

    /**
     * Get base URL
     * @returns {string}
     */
    getBaseUrl() {
        return this.baseUrl;
    }

    /**
     * Check if debug mode is enabled
     * @returns {boolean}
     */
    isDebug() {
        return this.debug;
    }
}

