/**
 * QwafelSystem Loader
 * This is the Main Entry For the App
 * Dynamically imports all page scripts based on the Laravel route name.
 * Loads global components on every page automatically.
 */
// import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './app.js'; 

App.config.loader = {
    /**
     * Define all your global components that should load everywhere.
     */
    components: [
    ],

    /**
     * Define optional page-specific scripts.
     * Keys must match Laravel route names (Route::currentRouteName()).
     */
    pages: {
        'dashboard': ['Pages/Dashboard.js'],
    },

    /**
     * Load all files for the current page + global components.
     */
    async init() {
        const route = document.body.dataset.route;
        const globalFiles = this.components;
        const pageFiles = this.pages[route] || [];
        const filesToLoad = [...globalFiles, ...pageFiles];

        if (App.config.debug) {
            console.log(`[Loader] Current route: ${route}`);
            console.log(`[Loader] Files to load:`, filesToLoad);
        }

        for (const file of filesToLoad) {
            try {
                await import(/* @vite-ignore */ `./${file}`);
                if (App.config.debug) console.log(`[Loader] Loaded: ${file}`);
            } catch (error) {
                console.warn(`[Loader] Failed to load: ${file}`, error);
            }
        }
    },
};


document.addEventListener('DOMContentLoaded', async () => {
    if (typeof App === 'undefined') {
        console.error('[Loader] App not found after import.');
        return;
    }
    App.init();
    if (App.config.debug) {
        console.log('[Loader] App initialized');
    }
    if (App.config?.loader) {
        await App.config.loader.init();
        console.log('[Loader] Page scripts loaded');
    }
});
