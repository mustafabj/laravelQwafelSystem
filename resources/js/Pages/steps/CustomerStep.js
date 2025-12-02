import { Modal } from 'bootstrap';

App.pages = App.pages || {};

App.pages.OrderWizard = App.pages.OrderWizard || {};

App.pages.OrderWizard.CustomerStep = {
    searchController: null,
    searchTimeout: null,
    debounceDelay: 300,
    isSelecting: false,

    bindSearchCustomer() {
        const input = document.getElementById("search-customer");
        const tbody = document.getElementById("customerBody");
        const addCustomerBtn = document.getElementById("addCustomer");
        
        if (!input || !tbody) return;

        input.addEventListener("keyup", () => {
            this.handleSearch(input, tbody);
        });

        input.addEventListener("input", () => { 
            this.handleSearch(input, tbody);
        });

        // Bind add customer button
        if (addCustomerBtn) {
            addCustomerBtn.addEventListener("click", () => {
                this.showAddCustomerModal();
            });
        }
    },

    handleSearch(input, tbody) {
        const query = input.value.trim();

        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Cancel previous search request
        if (this.searchController) {
            this.searchController.abort();
        }

        // If query is too short, show initial empty state immediately
        if (query.length < 2) {
            this.showState(tbody, 'initial');
            return;
        }

        // Show loading state
        this.showState(tbody, 'loading');

        // Debounce the search
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query, tbody);
        }, this.debounceDelay);
    },

    async showState(tbody, state) {
        try {
            const res = await App.utils.ajax(route("getCustomers"), {
                method: "POST",
                body: JSON.stringify({ state }),
            });

            if (res.html) {
                requestAnimationFrame(() => {
                    tbody.innerHTML = res.html;
                });
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[CustomerStep] Failed to load state:', state, err);
            }
        }
    },

    async performSearch(query, tbody) {
        this.searchController = new AbortController();

        try {
            const res = await App.utils.ajax(route("getCustomers"), {
                method: "POST",
                body: JSON.stringify({ search: query }),
                signal: this.searchController.signal,
            });

            // Update table with server response
            requestAnimationFrame(() => {
                if (res.html && res.html.trim() !== "") {
                    tbody.innerHTML = res.html;
                } else {
                    this.showState(tbody, 'no-results');
                }
            });
        } catch (err) {
            if (err.name !== "AbortError") {
                this.handleSearchError(tbody);
            }
        } finally {
            this.searchController = null;
        }
    },

    handleSearchError(tbody) {
        App.utils.showToast("فشل البحث عن العملاء", "error");
        this.showState(tbody, 'no-results');
    },

    async selectCustomer(id, wizard) {
        // Prevent multiple simultaneous selections
        if (this.isSelecting) {
            return;
        }

        this.isSelecting = true;

        const row = document.querySelector(`table tbody tr[data-id="${id}"]`);
        
        // Disable all rows during selection
        document.querySelectorAll('table tbody tr').forEach(tr => {
            tr.classList.remove('selected');
            tr.style.pointerEvents = 'none';
            tr.style.opacity = '0.6';
        });
        
        if (row) {
            row.classList.add('selected');
            row.style.animation = 'pulse 0.3s ease';
            setTimeout(() => {
                row.style.animation = '';
            }, 300);
        }

        try {
            // Load customer data and show phone step (wait for data)
            await wizard.loadCustomerData(id);
            
            // Re-enable all rows immediately after data is loaded
            document.querySelectorAll('table tbody tr').forEach(tr => {
                tr.style.pointerEvents = '';
                tr.style.opacity = '';
            });
            this.isSelecting = false;
            
            // Show success feedback
            App.utils.showToast('تم اختيار العميل بنجاح', 'success');
            
            // Advance immediately after data is loaded
            wizard.nextStep();
        } catch (err) {
            // Re-enable rows on error
            document.querySelectorAll('table tbody tr').forEach(tr => {
                tr.style.pointerEvents = '';
                tr.style.opacity = '';
            });
            this.isSelecting = false;
            
            if (App.config?.debug) {
                console.error('[CustomerStep] Failed to select customer:', err);
            }
            App.utils.showToast("فشل اختيار العميل", "error");
        }
    },

    async loadCustomerData(customerId, wizard) {
        try {
            const res = await App.utils.ajax(route("getCustomer"), {
                method: "POST",
                body: JSON.stringify({ customerId }),
            });

            // Store customer data
            wizard.selectedCustomer = {
                id: res.customerId,
                fName: res.FName,
                lName: res.LName,
            };

            // Store addresses for later use
            wizard.customerAddresses = res.addresses || [];

            // Load phone numbers
            wizard.loadPhoneNumbers([
                res.phone1,
                res.phone2,
                res.phone3,
                res.phone4,
            ]);

            // Update name fields
            const fnameInput = document.getElementById("fname");
            const lnameInput = document.getElementById("lname");
            if (fnameInput) fnameInput.value = res.FName;
            if (lnameInput) lnameInput.value = res.LName;
        } catch (err) {
            App.utils.showToast("فشل تحميل بيانات العميل", "error");
        }
    },

    showAddCustomerModal() {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        const modalDialog = modalElement?.querySelector('.modal-dialog');
        const template = document.getElementById('customerModalTemplate');
        
        if (!modalElement || !modalBody || !template) {
            App.utils.showToast("خطأ في فتح النافذة", "error");
            return;
        }

        // Set larger modal size for customer form (two sections side by side)
        if (modalDialog) {
            modalDialog.classList.remove('modal-sm', 'modal-md', 'modal-lg', 'modal-xl');
            modalDialog.classList.add('modal-xl');
        }

        // Clone template content
        const templateContent = template.content.cloneNode(true);
        modalBody.innerHTML = '';
        modalBody.appendChild(templateContent);
        
        // Initialize Bootstrap modal and show immediately
        const modalInstance = new Modal(modalElement, {
            backdrop: true,
            keyboard: true
        });
        
        // Show modal immediately without waiting
        requestAnimationFrame(() => {
            modalInstance.show();
            document.body.classList.add('modal-open');
        });

        // Pre-fill phone number from search if available
        const searchInput = document.getElementById("search-customer");
        const phoneInput = document.getElementById("phoneNumber");
        if (searchInput && phoneInput && searchInput.value.trim()) {
            phoneInput.value = searchInput.value.trim();
        }

        // Bind modal events
        this.bindCustomerModal(modalInstance, modalElement);
    },

    bindCustomerModal(modalInstance, modalElement) {
        const closeBtn = document.getElementById('closeCustomerModal');
        const cancelBtn = document.getElementById('cancelCustomerBtn');
        const submitBtn = document.getElementById('submitCustomerBtn');
        const form = document.getElementById('addCustomerForm');

        // Handle modal close events
        const closeModal = () => {
            modalInstance.hide();
            document.body.classList.remove('modal-open');
        };

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        // Cancel button
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }

        // Handle modal hidden event
        modalElement.addEventListener('hidden.bs.modal', () => {
            document.body.classList.remove('modal-open');
            const modalBody = document.getElementById('appModalBody');
            const modalDialog = modalElement?.querySelector('.modal-dialog');
            if (modalBody) {
                modalBody.innerHTML = '';
            }
            // Reset modal size to default
            if (modalDialog) {
                modalDialog.classList.remove('modal-xl');
                modalDialog.classList.add('modal-lg');
            }
        }, { once: true });

        // Submit button
        if (submitBtn && form) {
            submitBtn.addEventListener('click', async () => {
                await this.submitCustomerForm(modalInstance, modalElement);
            });
        }
    },

    async submitCustomerForm(modalInstance, modalElement) {
        const form = document.getElementById('addCustomerForm');
        if (!form) return;

        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Disable submit button
        const submitBtn = document.getElementById('submitCustomerBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'جاري الحفظ...';
        }

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            const res = await App.utils.ajax(route("storeCustomer"), {
                method: "POST",
                body: JSON.stringify(data),
            });

            if (res.success) {
                // Close modal
                modalInstance.hide();
                document.body.classList.remove('modal-open');

                // Clear form
                form.reset();

                // Refresh customer search to show new customer
                const searchInput = document.getElementById("search-customer");
                const tbody = document.getElementById("customerBody");
                if (searchInput && tbody) {
                    // Trigger search to refresh list
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        this.performSearch(query, tbody);
                    } else {
                        // If no search query, search for the new customer's phone
                        const phoneNumber = data.phoneNumber;
                        if (phoneNumber) {
                            searchInput.value = phoneNumber;
                            this.performSearch(phoneNumber, tbody);
                        }
                    }
                }

                App.utils.showToast(res.message || "تم اضافة العميل بنجاح", "success");
            } else {
                App.utils.showToast("فشل اضافة العميل", "error");
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[CustomerStep] Failed to submit customer form:', err);
            }
            App.utils.showToast("فشل اضافة العميل", "error");
        } finally {
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'اضافة';
            }
        }
    },
};

