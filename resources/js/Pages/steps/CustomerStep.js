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
        if (!input || !tbody) return;

        input.addEventListener("keyup", () => {
            this.handleSearch(input, tbody);
        });

        input.addEventListener("input", () => { 
            this.handleSearch(input, tbody);
        });
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
};

