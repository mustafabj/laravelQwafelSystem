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
            row.addEventListener('click', () => {
                this.showParcelModal(row.dataset.parcelId);
            });
        });

        // Ticket row clicks
        document.querySelectorAll('[data-ticket-id]').forEach(row => {
            row.addEventListener('click', () => {
                this.showTicketModal(row.dataset.ticketId);
            });
        });
    }

    /**
     * Show parcel details in modal
     * @param {string} id - Parcel ID
     */
    async showParcelModal(id) {
        const modal = new Modal(document.getElementById('appModal'));
        const modalBody = document.getElementById('appModalBody');
        modal.show();

        try {
            const response = await App.utils.ajax(route('parcel.show'), {
                method: 'POST',
                body: JSON.stringify({ id }),
            });
            modalBody.innerHTML = response.html;
        } catch (err) {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">خطأ في الاتصال بالخادم</div>';
        }
    }

    /**
     * Show ticket details in modal
     * @param {string} id - Ticket ID
     */
    async showTicketModal(id) {
        const modal = new Modal(document.getElementById('appModal'));
        const modalBody = document.getElementById('appModalBody');
        modal.show();

        try {
            const response = await App.utils.ajax(route('ticket.show'), {
                method: 'POST',
                body: JSON.stringify({ id }),
            });

            modalBody.innerHTML = response.html || '<div class="text-danger text-center py-5">تعذر تحميل البيانات</div>';
        } catch (error) {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">حدث خطأ في تحميل البيانات</div>';
        }
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
