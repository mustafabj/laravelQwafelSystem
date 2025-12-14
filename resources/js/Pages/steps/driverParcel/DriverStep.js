/**
 * DriverStep Module
 * Handles driver selection step in DriverParcelWizard
 */
import { Modal } from 'bootstrap';

App.pages = App.pages || {};
App.pages.DriverParcelWizard = App.pages.DriverParcelWizard || {};

App.pages.DriverParcelWizard.DriverStep = {
    searchController: null,
    searchTimeout: null,
    debounceDelay: 300,
    isSelecting: false,

    bindDriverSearch() {
        const input = document.getElementById('search-driver');
        const tbody = document.getElementById('driverBody');
        const addDriverBtn = document.getElementById('addDriver');
        
        if (!input || !tbody) return;

        input.addEventListener('keyup', () => {
            this.handleSearch(input, tbody);
        });

        input.addEventListener('input', () => { 
            this.handleSearch(input, tbody);
        });

        // Bind select driver buttons (event delegation)
        tbody.addEventListener('click', (e) => {
            const selectBtn = e.target.closest('.select-driver-btn');
            if (selectBtn) {
                const driverId = parseInt(selectBtn.dataset.driverId);
                if (!isNaN(driverId)) {
                    const wizard = App.pages.DriverParcelWizard;
                    this.selectDriver(driverId, wizard);
                }
            }
        });

        // Bind add driver button
        if (addDriverBtn) {
            addDriverBtn.addEventListener('click', () => {
                this.showAddDriverModal();
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
            const res = await App.utils.ajax(route('drivers.search'), {
                method: 'POST',
                body: JSON.stringify({ state }),
            });

            if (res.html) {
                requestAnimationFrame(() => {
                    tbody.innerHTML = res.html;
                });
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[DriverStep] Failed to load state:', state, err);
            }
        }
    },

    async performSearch(query, tbody) {
        this.searchController = new AbortController();

        try {
            const res = await App.utils.ajax(route('drivers.search'), {
                method: 'POST',
                body: JSON.stringify({ search: query }),
                signal: this.searchController.signal,
            });

            // Update table with server response
            requestAnimationFrame(() => {
                if (res.html && res.html.trim() !== '') {
                    tbody.innerHTML = res.html;
                } else {
                    this.showState(tbody, 'no-results');
                }
            });
        } catch (err) {
            if (err.name !== 'AbortError') {
                if (App.config?.debug) {
                    console.error('[DriverStep] Search failed:', err);
                }
                this.showState(tbody, 'error');
            }
        }
    },

    async selectDriver(driverId, wizard) {
        // Prevent multiple simultaneous selections
        if (this.isSelecting) {
            return;
        }

        this.isSelecting = true;

        const row = document.querySelector(`table tbody tr[data-id="${driverId}"]`);
        
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
            const response = await fetch(`/drivers/${driverId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const res = await response.json();

            if (res.success && res.driver) {
                wizard.selectedDriver = res.driver;
                this.displaySelectedDriver(res.driver, wizard);
                
                // Re-enable all rows immediately after data is loaded
                document.querySelectorAll('table tbody tr').forEach(tr => {
                    tr.style.pointerEvents = '';
                    tr.style.opacity = '';
                });
                this.isSelecting = false;
                
                // Show success feedback
                App.utils.showToast('تم اختيار السائق بنجاح', 'success');
                
                // Advance immediately after data is loaded
                if (wizard && typeof wizard.nextStep === 'function') {
                    wizard.nextStep();
                }
            } else {
                // Re-enable rows on error
                document.querySelectorAll('table tbody tr').forEach(tr => {
                    tr.style.pointerEvents = '';
                    tr.style.opacity = '';
                });
                this.isSelecting = false;
                App.utils.showToast(res.message || 'فشل في تحميل بيانات السائق', 'error');
            }
        } catch (err) {
            // Re-enable rows on error
            document.querySelectorAll('table tbody tr').forEach(tr => {
                tr.style.pointerEvents = '';
                tr.style.opacity = '';
            });
            this.isSelecting = false;
            
            if (App.config?.debug) {
                console.error('[DriverStep] Failed to select driver:', err);
            }
            App.utils.showToast('فشل في تحميل بيانات السائق', 'error');
        }
    },

    displaySelectedDriver(driver, wizard) {
        // Update hidden fields or display
        const driverNameInput = document.getElementById('driverName');
        const driverNumberInput = document.getElementById('driverNumber');
        const driverIdInput = document.getElementById('driverId');

        if (driverNameInput) driverNameInput.value = driver.driverName || '';
        if (driverNumberInput) driverNumberInput.value = driver.driverPhone || '';
        if (driverIdInput) driverIdInput.value = driver.driverId || '';

        // Show selected driver info
        const selectedInfo = document.getElementById('selectedDriverInfo');
        if (selectedInfo) {
            selectedInfo.innerHTML = `
                <div class="selected-driver-card">
                    <i class="fas fa-user-tie"></i>
                    <div>
                        <strong>${driver.driverName}</strong>
                        <span>${driver.driverPhone}</span>
                    </div>
                </div>
            `;
            selectedInfo.style.display = 'block';
        }
    },

    showAddDriverModal() {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        const modalDialog = modalElement?.querySelector('.modal-dialog');
        const template = document.getElementById('driverModalTemplate');
        
        if (!modalElement || !modalBody || !template) {
            App.utils.showToast('خطأ في فتح النافذة', 'error');
            return;
        }

        // Set modal size
        if (modalDialog) {
            modalDialog.classList.remove('modal-sm', 'modal-md', 'modal-xl');
            modalDialog.classList.add('modal-lg');
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
        
        // Show modal immediately
        requestAnimationFrame(() => {
            modalInstance.show();
            document.body.classList.add('modal-open');
        });

        // Bind modal events
        this.bindDriverModal(modalInstance, modalElement);
    },

    bindDriverModal(modalInstance, modalElement) {
        const closeBtn = document.getElementById('closeDriverModal');
        const cancelBtn = document.getElementById('cancelDriverBtn');
        const submitBtn = document.getElementById('submitDriverBtn');
        const form = document.getElementById('addDriverForm');

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
            if (modalBody) {
                modalBody.innerHTML = '';
            }
        }, { once: true });

        // Submit button
        if (submitBtn && form) {
            submitBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                await this.submitDriverForm(modalInstance, modalElement);
            });
        }
    },

    async submitDriverForm(modalInstance, modalElement) {
        const form = document.getElementById('addDriverForm');
        if (!form) return;

        const submitBtn = document.getElementById('submitDriverBtn');
        const originalText = submitBtn.innerHTML;
        const isEdit = form.dataset.driverId !== undefined;
        const driverId = form.dataset.driverId;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

        try {
            const formData = new FormData(form);
            const data = {
                driverName: formData.get('driverName'),
                driverPhone: formData.get('driverPhone'),
            };

            const url = isEdit ? `/drivers/${driverId}` : '/drivers';
            const method = isEdit ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                App.utils.showToast(result.message || (isEdit ? 'تم تحديث السائق بنجاح' : 'تم إضافة السائق بنجاح'), 'success');
                
                // Close modal
                modalInstance.hide();
                document.body.classList.remove('modal-open');
                
                // Refresh search results
                const searchInput = document.getElementById('search-driver');
                const tbody = document.getElementById('driverBody');
                if (searchInput && tbody) {
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        this.performSearch(query, tbody);
                    } else {
                        this.showState(tbody, 'initial');
                    }
                }
                
                // Auto-select the newly created driver (only for new drivers)
                if (!isEdit && result.driverId) {
                    const wizard = App.pages.DriverParcelWizard;
                    await this.selectDriver(result.driverId, wizard);
                }
            } else {
                App.utils.showToast(result.message || 'حدث خطأ أثناء الحفظ', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error submitting driver form:', error);
            App.utils.showToast('حدث خطأ أثناء الحفظ', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    async editDriver(driverId) {
        try {
            const response = await fetch(`/drivers/${driverId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const res = await response.json();

            if (res.success && res.driver) {
                this.showEditDriverModal(res.driver);
            } else {
                App.utils.showToast(res.message || 'فشل في تحميل بيانات السائق', 'error');
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[DriverStep] Failed to load driver for edit:', err);
            }
            App.utils.showToast('فشل في تحميل بيانات السائق', 'error');
        }
    },

    showEditDriverModal(driver) {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        const modalDialog = modalElement?.querySelector('.modal-dialog');
        const template = document.getElementById('driverModalTemplate');
        
        if (!modalElement || !modalBody || !template) {
            App.utils.showToast('خطأ في فتح النافذة', 'error');
            return;
        }

        // Set modal size
        if (modalDialog) {
            modalDialog.classList.remove('modal-sm', 'modal-md', 'modal-xl');
            modalDialog.classList.add('modal-lg');
        }

        // Clone template content
        const templateContent = template.content.cloneNode(true);
        modalBody.innerHTML = '';
        modalBody.appendChild(templateContent);
        
        // Update modal title and form for edit
        const modalTitle = modalBody.querySelector('.modal-title');
        const form = modalBody.querySelector('#addDriverForm');
        const submitBtn = modalBody.querySelector('#submitDriverBtn');
        
        if (modalTitle) {
            modalTitle.textContent = 'تعديل السائق';
        }
        if (form) {
            form.dataset.driverId = driver.driverId;
            form.querySelector('#driverName').value = driver.driverName || '';
            form.querySelector('#driverPhone').value = driver.driverPhone || '';
        }
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-save"></i> حفظ التعديلات';
        }
        
        // Initialize Bootstrap modal and show immediately
        const modalInstance = new Modal(modalElement, {
            backdrop: true,
            keyboard: true
        });
        
        // Show modal immediately
        requestAnimationFrame(() => {
            modalInstance.show();
            document.body.classList.add('modal-open');
        });

        // Bind modal events
        this.bindDriverModal(modalInstance, modalElement);
    }
};

