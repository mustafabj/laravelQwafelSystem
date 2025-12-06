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
        'wizard': [
            'Pages/steps/CustomerStep.js',
            'Pages/steps/PhoneStep.js',
            'Pages/steps/AddressStep.js',
            'Pages/steps/FormStep.js',
            'Pages/OrderWizard.js',
        ],
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
        
        // Separate step files from main page file
        const stepFiles = pageFiles.filter(file => file.includes('/steps/'));
        const mainPageFile = pageFiles.find(file => !file.includes('/steps/'));
        const filesToLoad = [...globalFiles, ...stepFiles];
        
        if (mainPageFile) {
            filesToLoad.push(mainPageFile);
        }

        if (App.config.debug) {
            console.log(`[Loader] Current route: ${route}`);
            console.log(`[Loader] Files to load:`, filesToLoad);
        }

        // Load files sequentially to ensure step files are loaded before main file
        for (const file of filesToLoad) {
            try {
                const modulePath = `./${file}`;
                if (modules[modulePath]) {
                    await modules[modulePath]();
                    if (App.config.debug) {
                        console.log(`[Loader] Loaded: ${file}`);
                    }
                } else {
                    console.warn(`[Loader] Module not found in bundle: ${file}`);
                }
            } catch (error) {
                console.warn(`[Loader] Failed to load: ${file}`, error);
            }
        }

        // Small delay to ensure all namespace assignments are complete
        await new Promise(resolve => setTimeout(resolve, 10));

        // Initialize main page file after all dependencies are loaded
        if (mainPageFile) {
            const moduleName = mainPageFile.replace('Pages/', '').replace('.js', '');
                if (App.pages && App.pages[moduleName] && typeof App.pages[moduleName].init === 'function') {
                // Verify step modules are available before initializing
                if (App.config.debug) {
                    console.log('[Loader] Step modules available:', {
                        FormStep: !!App.pages.OrderWizard?.FormStep,
                        CustomerStep: !!App.pages.OrderWizard?.CustomerStep,
                        PhoneStep: !!App.pages.OrderWizard?.PhoneStep,
                    });
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
