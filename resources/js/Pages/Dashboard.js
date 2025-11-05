App.pages = App.pages || {};

App.pages.dashboard = {
    init() {
        this.bindEvents();
    },

    // Bind UI events for the dashboard
    bindEvents() {
        const table = document.querySelector('#parcelsTable');
        if (!table) return;

        table.addEventListener('click', (e) => {
            const row = e.target.closest('[data-parcel-id]');
            if (!row) return;
            this.getParelsById(row.dataset.parcelId);
        });
    },

    async getParelsById(id) {
        const response = await App.utils.ajax(`${App.config.baseUrl}/parcel/show`, {
            method: 'POST',
            body: JSON.stringify({ id }),
        });
    
        if (response && typeof response === 'object' && response.html) {
            document.querySelector('#bodyForm').innerHTML = response.html;
        }
    },
};

App.pages.dashboard.init();

