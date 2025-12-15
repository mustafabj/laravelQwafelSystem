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

    init() {
        if (this._initialized) return;
        this._initialized = true;

        if (this.config.debug) {
            console.log('[App] Initialized');
            console.log('[App] CSRF:', this.config.csrfToken);
        }
    }
};

window.App = App;

export default App;
