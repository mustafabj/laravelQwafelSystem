App.pages = App.pages || {};

import Stepper from 'bs-stepper';

App.pages.OrderWizard = {
    stepper: null,
    currentType: null, // 'parcel' or 'ticket'

    init() {
        const el = document.querySelector('#orderStepper');
        if (!el) return;

        this.stepper = new Stepper(el, {
            linear: false,
            animation: true,
        });
        console.log('[OrderWizard] Stepper initialized');
        this.bindNavButtons();
        this.initDateTimeDefaults();
        this.bindTypeButtons();
        this.bindSearchCustomer();
        // You can add more bindings: load phones, addresses etc.
    },

    bindNavButtons() {
        // Next buttons
        document.querySelectorAll('[data-wizard-next]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.nextStep();
            });
        });

        // Previous buttons
        document.querySelectorAll('[data-wizard-prev]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.prevStep();
            });
        });
    },

    nextStep() {
        this.stepper.next();
        this.syncTabsHeader();
    },

    prevStep() {
        const idx = this.stepper._currentIndex;
        if (idx === 0) {
            // same behavior as your old code: go back to home/index
            window.location.href = '/';
            return;
        }
        this.stepper.previous();
        this.syncTabsHeader();
    },

    syncTabsHeader() {
        const idx = this.stepper._currentIndex;
        const tabs = document.querySelectorAll('.tabs ul li');
        tabs.forEach((li, i) => {
            li.classList.toggle('active', i === idx);
        });
    },

    initDateTimeDefaults() {
        // same as your old code: set min date, default time, etc.
        const dateInput = document.getElementById('datact');
        if (dateInput) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const todayFormatted = `${yyyy}-${mm}-${dd}`;

            dateInput.min = today.toISOString().split('T')[0];
            dateInput.value = todayFormatted;
        }

        const timeInput = document.getElementById('timect');
        if (timeInput) {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const min = String(now.getMinutes()).padStart(2, '0');
            timeInput.value = `${hh}:${min}`;
        }
    },

    bindTypeButtons() {
        const parcelBtn = document.querySelector('[data-order-type="parcel"]');
        const ticketBtn = document.querySelector('[data-order-type="ticket"]');
        const submitBtn = document.getElementById('wizardSubmitBtn');

        if (parcelBtn) {
            parcelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.currentType = 'parcel';
                this.loadForm('parcel');
                this.nextStep();
            });
        }

        if (ticketBtn) {
            ticketBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.currentType = 'ticket';
                this.loadForm('ticket');
                this.nextStep();
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitCurrentForm();
            });
        }
    },

    async loadForm(type) {
        const container = document.getElementById('orderStepFormContainer');
        if (!container) return;

        container.innerHTML = '<div class="text-center py-4">Loading...</div>';

        try {
            const url = type === 'parcel'
                ? route('wizard.parcel.form')
                : route('wizard.ticket.form');

            const res = await App.utils.ajax(url, { method: 'GET' });
            container.innerHTML = res.html || '';
        } catch (err) {
            container.innerHTML = '<div class="text-danger text-center py-4">خطأ في تحميل النموذج</div>';
        }
    },

    async submitCurrentForm() {
        const type = this.currentType;
        if (!type) return;

        const form = document.querySelector(
            type === 'parcel' ? '#saveParcel' : '#saveTicket'
        );
        if (!form) return;

        const formData = Object.fromEntries(new FormData(form).entries());

        try {
            const url = type === 'parcel'
                ? route('wizard.parcel.save')
                : route('wizard.ticket.save');

            const res = await App.utils.ajax(url, {
                method: 'POST',
                body: JSON.stringify(formData),
            });

            // res should contain HTML for print view, example: { html: '...' }
            const printContainer = document.getElementById('orderPrintContainer');
            if (printContainer && res.html) {
                printContainer.innerHTML = res.html;
            }

            this.nextStep();
        } catch (err) {
            App.utils.showToast('حدث خطأ أثناء الحفظ', 'error');
        }
    },

    bindSearchCustomer() {
        const form = document.getElementById('search-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('search-customer');
            const query = input?.value || '';

            try {
                const res = await App.utils.ajax(route('wizard.customers'), {
                    method: 'POST',
                    body: JSON.stringify({ search: query }),
                });

                const tbody = document.getElementById('customerBody');
                if (tbody && res.html) {
                    tbody.innerHTML = res.html;
                }
            } catch (err) {
                App.utils.showToast('فشل في البحث عن العملاء', 'error');
            }
        });
    }
};

App.pages.OrderWizard.init();

