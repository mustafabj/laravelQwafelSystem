/**
 * Layout Component
 * Handles sidebar toggle, user menu, and layout interactions
 */

class LayoutComponent {
    constructor() {
        this.initialized = false;
    }

    /**
     * Initialize layout functionality
     * Prevents duplicate initialization
     */
    init() {
        if (this.initialized) {
            if (App.config.debug) {
                console.log('[Layout] Already initialized, skipping');
            }
            return;
        }

        this.initSidebar();
        this.initUserMenu();
        this.handleResize();
        this.initialized = true;

        if (App.config.debug) {
            console.log('[Layout] Initialized');
        }
    }

    /**
     * Initialize sidebar toggle functionality
     */
    initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');

        if (!sidebar || !sidebarToggle) {
            return;
        }

        const toggleSidebar = () => {
            if (window.innerWidth <= 1024) {
                sidebar.classList.toggle('mobile-open');
                mobileOverlay?.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        };

        sidebarToggle.addEventListener('click', toggleSidebar);

        mobileOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('active');
        });
    }

    /**
     * Initialize user menu dropdown
     */
    initUserMenu() {
        const userMenu = document.getElementById('userMenu');
        const userButton = userMenu?.querySelector('.user-button');

        if (!userMenu || !userButton) {
            return;
        }

        // Toggle menu on button click
        userButton.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
    }

    /**
     * Handle window resize events
     * Uses debouncing to prevent excessive calls
     */
    handleResize() {
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const sidebar = document.getElementById('sidebar');
                const mobileOverlay = document.getElementById('mobileOverlay');

                if (window.innerWidth > 1024) {
                    sidebar?.classList.remove('mobile-open');
                    mobileOverlay?.classList.remove('active');
                }
            }, 150); // Debounce resize events
        });
    }
}

// Create instance and attach to App.components for backward compatibility
const layoutComponent = new LayoutComponent();
App.components = App.components || {};
App.components.Layout = layoutComponent;

export default layoutComponent;
