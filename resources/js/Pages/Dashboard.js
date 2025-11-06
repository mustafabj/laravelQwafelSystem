
App.pages = App.pages || {};
import { Modal } from 'bootstrap';

App.pages.dashboard = {
    init() {
        this.bindEvents();
    },

    // Bind UI events for the dashboard
    bindEvents() {
        const rows = document.querySelectorAll('[data-parcel-id]');
        rows.forEach(row => {
            row.addEventListener('click', () => {
                this.showParcelModal(row.dataset.parcelId);
            });
        });
    },

    async showParcelModal(id) {
        const modalEl = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        const modalTitle = document.getElementById('appModalLabel');
        const bsModal = new Modal(modalEl);

        modalTitle.textContent = 'تفاصيل الإرسالية';
        modalBody.innerHTML = '<div class="text-center py-5 text-muted">Loading...</div>';
        bsModal.show();

        try {
            const response = await App.utils.ajax(route('parcel.show'), {
                method: 'POST',
                body: JSON.stringify({ id }),
            });

            modalBody.innerHTML = response.html ?? '<div class="text-danger text-center py-5">تعذر تحميل البيانات.</div>';
        } catch (err) {
            modalBody.innerHTML = '<div class="text-danger text-center py-5">خطأ في الاتصال بالخادم</div>';
        }
    },

};
App.pages.dashboard.init();

