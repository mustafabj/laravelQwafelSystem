import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Echo/Pusher real-time communication manager
 */
export class EchoManager {
    constructor(config, logger, toast = null) {
        this.config = config;
        this.logger = logger;
        this.toast = toast;
        this.instance = null;
    }

    /**
     * Initialize Echo instance (lazy initialization)
     * @returns {Echo|null} Echo instance or null if initialization fails
     */
    init() {
        if (this.instance) {
            return this.instance;
        }

        try {
            this.instance = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                forceTLS: true,
            });

            this.subscribeToTestChannel();

            return this.instance;
        } catch (error) {
            this.logger.warn('Echo initialization failed:', error);
            return null;
        }
    }

    /**
     * Subscribe to test channel for development
     */
    subscribeToTestChannel() {
        if (!this.instance) {
            return;
        }

        this.instance
            .channel('test-channel')
            .listen('TestPusherEvent', (event) => {
                if (event.message && this.toast) {
                    this.toast.success(event.message);
                }
                this.logger.log('Pusher event received:', event);
            });
    }

    /**
     * Get Echo instance (initializes if needed)
     * @returns {Echo|null}
     */
    getInstance() {
        return this.instance || this.init();
    }
}

