App.pages = App.pages || {};


import { Modal } from 'bootstrap';

App.pages.dashboard = {
    init() {
        this.bindEvents();
    },
    // Bind UI events for the dashboard
    bindEvents() {
        document.querySelectorAll('[data-parcel-id]').forEach(row => {
            row.addEventListener('click', () => {
                this.showParcelModal(row.dataset.parcelId);
            });
        });
        document.querySelectorAll("[data-ticket-id]").forEach(row => {
            row.addEventListener("click", () => {
                this.showTicketModal(row.dataset.ticketId);
            });
        });

    },
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
    },
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
    },
    
};
App.pages.dashboard.init();

