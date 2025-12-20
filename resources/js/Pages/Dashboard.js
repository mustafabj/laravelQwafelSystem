/**
 * Dashboard Page
 * Handles dashboard-specific functionality: tabs, search, filters, and table interactions
 */
import { Modal } from 'bootstrap';

class DashboardPage {
    constructor() {
        this.initialized = false;
    }

    /**
     * Initialize dashboard functionality
     * Prevents duplicate initialization
     */
    init() {
        if (this.initialized) {
            if (App.config.debug) {
                console.log('[Dashboard] Already initialized, skipping');
            }
            return;
        }

        this.initTabs();
        this.initFilters();
        this.bindEvents();
        this.initialized = true;

        if (App.config.debug) {
            console.log('[Dashboard] Initialized');
        }
    }

    /**
     * Initialize tab switching functionality
     */
    initTabs() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.dataset.tab;

                // Update buttons
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Update content
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                const targetTab = document.getElementById(tabName + '-tab');
                if (targetTab) {
                    targetTab.classList.add('active');
                }
            });
        });
    }

    /**
     * Initialize filter buttons
     */
    initFilters() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    }

    /**
     * Bind UI events for the dashboard
     */
    bindEvents() {
        // Parcel row clicks
        document.querySelectorAll('[data-parcel-id]').forEach(row => {
            row.addEventListener('click', (e) => {
                // Don't trigger if clicking on a link or button
                if (e.target.closest('a, button')) {
                    return;
                }
                this.showParcelModal(row.dataset.parcelId);
            });
        });

        // Ticket row clicks
        document.querySelectorAll('[data-ticket-id]').forEach(row => {
            row.addEventListener('click', (e) => {
                // Don't trigger if clicking on a link or button
                if (e.target.closest('a, button')) {
                    return;
                }
                this.showTicketModal(row.dataset.ticketId);
            });
        });

        // Driver Parcel row clicks
        document.querySelectorAll('[data-driver-parcel-id]').forEach(row => {
            row.addEventListener('click', (e) => {
                // Don't trigger if clicking on a link or button
                if (e.target.closest('a, button')) {
                    return;
                }
                this.showDriverParcelModal(row.dataset.driverParcelId);
            });
        });
    }

    /**
     * Show parcel details in modal
     * @param {string} id - Parcel ID
     */
    showParcelModal(id) {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        
        if (!modalElement || !modalBody) {
            return;
        }

        // Find the row with the parcel data
        const row = document.querySelector(`[data-parcel-id="${id}"]`);
        if (!row) {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">لم يتم العثور على البيانات</div>';
            const modal = new Modal(modalElement);
            modal.show();
            return;
        }

        // Get pre-rendered modal content from data attribute
        const modalContent = row.dataset.parcelModal;
        if (modalContent) {
            // Decode base64 content with proper UTF-8 handling
            try {
                // Use decodeURIComponent with escape to properly handle UTF-8
                const html = decodeURIComponent(escape(atob(modalContent)));
                modalBody.innerHTML = html;
            } catch (e) {
                modalBody.innerHTML = '<div class="text-danger text-center py-5">خطأ في تحميل البيانات</div>';
            }
        } else {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">لا توجد بيانات متاحة</div>';
        }

        // Show modal immediately
        const modal = new Modal(modalElement);
        modal.show();
    }

    /**
     * Show ticket details in modal
     * @param {string} id - Ticket ID
     */
    showTicketModal(id) {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        
        if (!modalElement || !modalBody) {
            return;
        }

        // Find the row with the ticket data
        const row = document.querySelector(`[data-ticket-id="${id}"]`);
        if (!row) {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">لم يتم العثور على البيانات</div>';
            const modal = new Modal(modalElement);
            modal.show();
            return;
        }

        // Get pre-rendered modal content from data attribute
        const modalContent = row.dataset.ticketModal;
        if (modalContent) {
            // Decode base64 content with proper UTF-8 handling
            try {
                // Use decodeURIComponent with escape to properly handle UTF-8
                const html = decodeURIComponent(escape(atob(modalContent)));
                modalBody.innerHTML = html;
            } catch (e) {
                modalBody.innerHTML = '<div class="text-danger text-center py-5">خطأ في تحميل البيانات</div>';
            }
        } else {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">لا توجد بيانات متاحة</div>';
        }

        // Show modal immediately
        const modal = new Modal(modalElement);
        modal.show();
    }

    /**
     * Show driver parcel details in modal
     * @param {string} id - Driver Parcel ID
     */
    showDriverParcelModal(id) {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        
        if (!modalElement || !modalBody) {
            return;
        }

        const row = document.querySelector(`[data-driver-parcel-id="${id}"]`);
        if (!row) {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">لم يتم العثور على البيانات</div>';
            const modal = new Modal(modalElement);
            modal.show();
            return;
        }

        const modalContent = row.dataset.driverParcelModal;
        if (modalContent) {
            try {
                const binaryString = atob(modalContent);
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }
                const html = new TextDecoder('utf-8').decode(bytes);
                modalBody.innerHTML = html;
            } catch (e) {
                modalBody.innerHTML = '<div class="text-danger text-center py-5">خطأ في تحميل البيانات</div>';
            }
        } else {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">لا توجد بيانات متاحة</div>';
        }

        // Show modal immediately
        const modal = new Modal(modalElement);
        modal.show();
    }

    /**
     * Search function for table filtering
     * Searches within the active tab's table
     */
    search() {
        const activeTab = document.querySelector('.tab-content.active');
        if (!activeTab) {
            return;
        }

        const searchInput = activeTab.querySelector('input[type="text"]');
        const table = activeTab.querySelector('.data-table');
        if (!searchInput || !table) {
            return;
        }

        const filter = searchInput.value.toUpperCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let found = false;

            cells.forEach(cell => {
                if (cell.textContent.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                }
            });

            row.style.display = found ? '' : 'none';
        });
    }

    /**
     * Handle filter change for table rows
     * @param {HTMLElement} selectedElement - The filter button element
     */
    handleFilterChange(selectedElement) {
        const activeTab = document.querySelector('.tab-content.active');
        if (!activeTab) {
            return;
        }

        const filter = selectedElement.dataset.filter || selectedElement.value;
        const table = activeTab.querySelector('.data-table');
        if (!table) {
            return;
        }

        const rows = table.querySelectorAll('tbody tr');
        const searchInput = activeTab.querySelector('input[type="text"]');

        if (filter !== 'all' && searchInput) {
            searchInput.value = '';
        }

        rows.forEach(row => {
            if (filter === 'all') {
                row.style.display = '';
                return;
            }

            const cells = row.querySelectorAll('td');
            let found = false;

            cells.forEach(cell => {
                if (cell.textContent.toUpperCase().indexOf(filter.toUpperCase()) > -1) {
                    found = true;
                }
            });

            row.style.display = found ? '' : 'none';
        });
    }
}

// Create instance and attach to App.pages for backward compatibility
const dashboardPage = new DashboardPage();
App.pages = App.pages || {};
App.pages.Dashboard = dashboardPage;

// Expose search and filter functions globally for inline event handlers
window.searchh = function() {
    App.pages.Dashboard.search();
};

window.handleFilterChange = function(element) {
    App.pages.Dashboard.handleFilterChange(element);
};

export default dashboardPage;
