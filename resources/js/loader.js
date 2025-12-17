/**
 * QwafelSystem Loader
 * This is the Main Entry For the App
 * Dynamically imports all page scripts based on the Laravel route name.
 * Loads global components on every page automatically.
 */
import './app.js';

// Pre-bundle all modules using Vite's glob import for production builds
const modules = import.meta.glob([
    './Components/Layout.js',
    './Pages/Dashboard.js',
    './Pages/steps/CustomerStep.js',
    './Pages/steps/PhoneStep.js',
    './Pages/steps/AddressStep.js',
    './Pages/steps/FormStep.js',
    './Pages/OrderWizard.js',
    './core/BaseWizard.js',
    './Pages/driverParcel/index.js',
    './Pages/DriverParcels.js',
    './Pages/Trips.js',
], { eager: false });

    /**
 * Loader configuration and initialization
     */
class Loader {
    constructor() {
        this.components = [
            'Components/Layout.js',
        ];

        this.pages = {
        'dashboard': ['Pages/Dashboard.js'],
        'orderWizard': [
            'Pages/steps/CustomerStep.js',
            'Pages/steps/PhoneStep.js',
            'Pages/steps/AddressStep.js',
            'Pages/steps/FormStep.js',
            'Pages/OrderWizard.js',
        ],
            'driver-parcels.create': [
                'Pages/driverParcel/index.js',
            ],
        'driver-parcels.index': ['Pages/DriverParcels.js'],
        'trips.index': ['Pages/Trips.js'],
        'trips.create': ['Pages/Trips.js'],
        };
    }

    /**
     * Load all files for the current page + global components.
     * Loads step files sequentially before main page file to ensure dependencies are ready.
     */
    async init() {
        const route = document.body.dataset.route;
        const globalFiles = this.components;
        const pageFiles = this.pages[route] || [];
        const stepFiles = pageFiles.filter(file => file.includes('/steps/'));
        const baseFiles = pageFiles.filter(file => file.includes('BaseWizard'));
        const mainPageFile = pageFiles.find(file => !file.includes('/steps/') && !file.includes('BaseWizard'));
        const filesToLoad = [...globalFiles, ...baseFiles, ...stepFiles, mainPageFile].filter(Boolean);

        // Load files sequentially to ensure step files are loaded before main file
        for (const file of filesToLoad) {
            try {
            const modulePath = `./${file}`;
                if (modules[modulePath]) {
                    const mod = await modules[modulePath]();
                    if (App.config.debug) {
                        console.log(`[Loader] Loaded: ${file}`);
                    }
                    
                    // If this is the main page file and it has a default export with init, call it
                    if (file === mainPageFile && mod?.default && typeof mod.default.init === 'function') {
                    if (App.config.debug) {
                            console.log(`[Loader] Initializing: ${file}`);
                        }
                        mod.default.init();
                    }
                } else {
                    console.warn(`[Loader] Module not found in bundle: ${file}`);
                }
            } catch (error) {
                console.error(`[Loader] Failed to load: ${file}`, error);
            }
        }

        // Small delay to ensure all namespace assignments are complete
        await new Promise(resolve => setTimeout(resolve, 10));

        // For old architecture (OrderWizard), also try calling init via App.pages
        if (mainPageFile && !mainPageFile.includes('index.js')) {
            const moduleName = mainPageFile.replace('Pages/', '').replace('.js', '');
            if (App.pages && App.pages[moduleName] && typeof App.pages[moduleName].init === 'function') {
                if (App.config.debug) {
                    console.log(`[Loader] Initializing via App.pages.${moduleName}`);
                }
                App.pages[moduleName].init();
                }
        }
    }
}

// Create loader instance and attach to App.config
const loader = new Loader();
App.config.loader = loader;

/**
 * Initialize the application
 */
    const initApp = async () => {
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
            
            App.initComponents();
            
            if (App.config.debug) {
                console.log('[Loader] Page scripts loaded and initialized');
            }
        }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
} else {
        initApp();
}

export default loader;
