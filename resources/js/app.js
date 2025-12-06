import Alpine from 'alpinejs';
import Pusher from 'pusher-js';
import { AppConfig } from './core/config.js';
import { Logger, Toast, Ajax } from './core/utils.js';
import { EchoManager } from './core/echo.js';

// Expose global libraries
window.Pusher = Pusher;
window.Alpine = Alpine;

/**
 * Main Application class
 * Maintains backward compatibility with global App object
 */
class App {
    constructor() {
        // Store config internally to avoid conflicts with proxy
        this._config = new AppConfig();
        this.logger = new Logger(this._config.isDebug());
        this.toast = new Toast();
        this._ajaxInstance = new Ajax(this._config.getCsrfToken());
        this.echoManager = new EchoManager(this._config, this.logger, this.toast);
        this.components = {};
        this.pages = {};
    }

    /**
     * Get the internal config instance
     * @returns {AppConfig}
     */
    get config() {
        return this._config;
    }

    /**
     * Initialize the application
     */
    init() {
        this.bindCommonEvents();
        this.initAlpine();
        this.initEcho();

        this.logger.log('App init');
        }

    /**
     * Initialize global components
     * Called after components are loaded by the loader
     */
    initComponents() {
        if (this.components?.Layout && typeof this.components.Layout.init === 'function') {
            this.components.Layout.init();
        }
    }

    /**
     * Initialize Alpine.js
     */
    initAlpine() {
        Alpine.start();
    }

    /**
     * Initialize Echo/Pusher for real-time updates
     */
    initEcho() {
        const echoInstance = this.echoManager.init();
        if (echoInstance) {
            // Store directly on the AppConfig instance
            this._config.setEcho(echoInstance);
        }
    }

    /**
     * Bind common global events
     */
    bindCommonEvents() {
        // Placeholder for common event bindings
    }

    /**
     * Legacy utils object for backward compatibility
     * @deprecated Use this.logger, this.toast, or this._ajaxInstance directly
     */
    get utils() {
        return {
            log: (...args) => this.logger.log(...args),
            showToast: (message, type) => this.toast.show(message, type),
            ajax: (url, options) => {
                return this._ajaxInstance.request(url, options).catch((err) => {
                    this.toast.error(err.message || 'Network error');
                    throw err;
                });
            },
        };
    }
}

// Create and expose global App instance
const appInstance = new App();
window.App = appInstance;

// Create a single persistent proxy object for backward compatibility
const configProxy = {
    get csrfToken() {
        return appInstance._config.getCsrfToken();
    },
    get baseUrl() {
        return appInstance._config.getBaseUrl();
    },
    get debug() {
        return appInstance._config.isDebug();
    },
    get echo() {
        return appInstance._config.getEcho();
    },
    set echo(value) {
        // Direct assignment to avoid recursion
        appInstance._config._echo = value;
    },
    get loader() {
        return appInstance._config.loader;
    },
    set loader(value) {
        appInstance._config.loader = value;
    },
};

// Override the config getter to return the proxy for backward compatibility
Object.defineProperty(window.App, 'config', {
    get() {
        return configProxy;
    },
    set(value) {
        // Allow setting loader property
        if (value && typeof value === 'object' && 'loader' in value) {
            appInstance._config.loader = value.loader;
        }
    },
    configurable: true,
    enumerable: true,
});

export default appInstance;
