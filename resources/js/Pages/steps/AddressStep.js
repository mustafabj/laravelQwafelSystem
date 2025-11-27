import { Modal } from 'bootstrap';

App.pages = App.pages || {};

App.pages.OrderWizard = App.pages.OrderWizard || {};

App.pages.OrderWizard.AddressStep = {
    selectedAddress: null,
    customerAddresses: [],

    async loadAddresses(addresses, wizard) {
        this.customerAddresses = addresses || [];
        // Also sync with wizard state
        if (wizard) {
            wizard.customerAddresses = this.customerAddresses;
        }
        const tbody = document.getElementById("addressBody");
        if (!tbody) return;

        // Remove empty state
        tbody.querySelectorAll('.empty-state').forEach(el => el.remove());

        if (!addresses || addresses.length === 0) {
            await this.showEmptyState(tbody);
            return;
        }

        // Render addresses from server
        await this.renderAddressRows(tbody, addresses);
    },

    async showEmptyState(tbody) {
        try {
            const res = await App.utils.ajax(route("getAddressEmptyState"), {
                method: "POST",
            });

            if (res.html) {
                requestAnimationFrame(() => {
                    tbody.innerHTML = res.html;
                });
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[AddressStep] Failed to load empty state:', err);
            }
        }
    },

    async renderAddressRows(tbody, addresses) {
        try {
            const res = await App.utils.ajax(route("getAddressRows"), {
                method: "POST",
                body: JSON.stringify({ addresses }),
            });

            if (res.html) {
                requestAnimationFrame(() => {
                    tbody.innerHTML = res.html;
                });
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[AddressStep] Failed to load address rows:', err);
            }
            await this.showEmptyState(tbody);
        }
    },

    bindAddressStep(wizard) {
        const tbody = document.getElementById("addressBody");
        const addBtn = document.getElementById("addAddressBtn");
        const officeBtn = document.getElementById("officeAddressBtn");

        if (!tbody) return;

        // Address row click handler
        tbody.addEventListener("click", (e) => {
            const row = e.target.closest("tr.address-row");
            if (!row) return;

            // Don't trigger on button clicks
            if (e.target.closest("button")) return;

            this.selectAddress(row, wizard);
        });

        // Edit button handler
        tbody.addEventListener("click", (e) => {
            const editBtn = e.target.closest(".btn-edit-address");
            if (editBtn) {
                e.stopPropagation(); // Prevent row selection
                const addressId = parseInt(editBtn.dataset.addressId);
                const address = this.customerAddresses.find(a => a.addressId === addressId);
                if (address) {
                    this.showEditAddressModal(address, wizard);
                }
            }
        });

        // Add address button
        if (addBtn) {
            addBtn.addEventListener("click", () => {
                this.showAddAddressModal(wizard);
            });
        }

        // Office address button
        if (officeBtn) {
            officeBtn.addEventListener("click", () => {
                this.selectOfficeAddress(wizard);
            });
        }
    },

    async showAddressModal(wizard, mode = 'add', address = null) {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        
        if (!modalElement || !modalBody) {
            App.utils.showToast("خطأ في فتح النافذة", "error");
            return;
        }

        try {
            // Load modal HTML from server
            const res = await App.utils.ajax(route("getAddressModal"), {
                method: "POST",
                body: JSON.stringify({
                    mode: mode,
                    address: address || {},
                }),
            });

            if (!res.html) {
                App.utils.showToast("فشل تحميل النافذة", "error");
                return;
            }

            // Inject HTML
            modalBody.innerHTML = res.html;

            // Show modal
            const modal = new Modal(modalElement, {
                backdrop: true,
                keyboard: true
            });
            modal.show();

            // Prevent body scroll when modal is open
            document.body.classList.add('modal-open');

            // Bind modal handlers
            this.bindAddressModal(modal, modalElement, modalBody, wizard, mode, address);
        } catch (err) {
            if (App.config?.debug) {
                console.error('[AddressStep] Failed to load address modal:', err);
            }
            App.utils.showToast("فشل تحميل النافذة", "error");
        }
    },

    bindAddressModal(modal, modalElement, modalBody, wizard, mode, address) {
        const closeBtn = document.getElementById('closeAddressModal');
        const cancelBtn = document.getElementById('cancelAddressBtn');
        const submitBtn = document.getElementById('submitAddressBtn');

        const closeModal = () => {
            modal.hide();
            document.body.classList.remove('modal-open');
        };

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }

        // Handle modal hidden event
        modalElement.addEventListener('hidden.bs.modal', () => {
            document.body.classList.remove('modal-open');
            modalBody.innerHTML = '';
        }, { once: true });

        // Submit button
        if (submitBtn) {
            submitBtn.addEventListener('click', async () => {
                if (mode === 'edit' && address) {
                    await this.submitEditAddressForm(address.addressId, wizard, modal, modalElement);
                } else {
                    await this.submitAddressForm(wizard, modal, modalElement);
                }
            });
        }
    },

    showAddAddressModal(wizard) {
        this.showAddressModal(wizard, 'add');
    },

    showEditAddressModal(address, wizard) {
        this.showAddressModal(wizard, 'edit', address);
    },

    async selectAddress(row, wizard) {
        // Remove previous selection
        document.querySelectorAll("tr.address-row").forEach((r) => {
            r.classList.remove("selected");
        });

        // Add selection immediately for visual feedback
        row.classList.add("selected");

        const addressId = parseInt(row.dataset.addressId);
        const address = this.customerAddresses.find(a => a.addressId === addressId);

        if (!address) {
            App.utils.showToast("العنوان غير موجود", "error");
            row.classList.remove("selected");
            return;
        }

        // Store address immediately
        this.selectedAddress = {
            addressId: address.addressId,
            city: address.city,
            area: address.area,
            street: address.street,
            buildingNumber: address.buildingNumber,
            info: address.info,
        };

        wizard.selectedAddress = this.selectedAddress;

        // Advance immediately
        wizard.currentType = 'ticket';
        wizard.currentStep = 4;
        wizard.showStep(4);
        wizard.tabshead(4);

        // Show success feedback
        App.utils.showToast("تم اختيار العنوان بنجاح", "success");

        // Load form in background (non-blocking)
        if (App.pages.OrderWizard.FormStep) {
            App.pages.OrderWizard.FormStep.loadForm('ticket', wizard).catch(err => {
                if (App.config?.debug) {
                    console.error('[AddressStep] Failed to load form:', err);
                }
            });
        }
    },

    selectOfficeAddress(wizard) {
        // Set office address (addressId = 1 or special marker)
        this.selectedAddress = {
            addressId: 1,
            city: "",
            area: "",
            street: "",
            buildingNumber: "",
            info: "من المكتب",
        };

        wizard.selectedAddress = this.selectedAddress;

        App.utils.showToast("تم اختيار العنوان من المكتب", "success");

        wizard.nextStep();
    },

    async submitAddressForm(wizard, modalInstance, modalElement) {
        const form = document.getElementById("addAddressForm");
        if (!form) return;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!wizard.selectedCustomer || !wizard.selectedCustomer.id) {
            App.utils.showToast("الرجاء اختيار عميل أولاً", "warning");
            return;
        }

        const formData = {
            customerId: wizard.selectedCustomer.id,
            city: document.getElementById("addressCity")?.value.trim() || '',
            area: document.getElementById("addressArea")?.value.trim() || '',
            street: document.getElementById("addressStreet")?.value.trim() || '',
            buildingNumber: document.getElementById("addressBuilding")?.value.trim() || '',
            info: document.getElementById("addressInfo")?.value.trim() || '',
        };

        try {
            const res = await App.utils.ajax(route("storeAddress"), {
                method: "POST",
                body: JSON.stringify(formData),
            });

            if (res.success && res.address) {
                // Add new address to list
                this.customerAddresses.push(res.address);
                // Also update wizard state
                wizard.customerAddresses = this.customerAddresses;
                
                // Reload addresses display
                this.loadAddresses(this.customerAddresses, wizard);

                // Close modal
                if (modalInstance) {
                    modalInstance.hide();
                }
                document.body.classList.remove('modal-open');

                App.utils.showToast("تم إضافة العنوان بنجاح", "success");

                // Auto-select the new address
                setTimeout(() => {
                    const newRow = document.querySelector(`tr[data-address-id="${res.address.addressId}"]`);
                    if (newRow) {
                        this.selectAddress(newRow, wizard);
                    }
                }, 300);
            } else {
                App.utils.showToast("فشل إضافة العنوان", "error");
            }
        } catch (err) {
            App.utils.showToast("فشل إضافة العنوان", "error");
        }
    },

    async submitEditAddressForm(addressId, wizard, modalInstance, modalElement) {
        const form = document.getElementById("editAddressForm");
        if (!form) return;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = {
            addressId: addressId,
            city: document.getElementById("editAddressCity")?.value.trim() || '',
            area: document.getElementById("editAddressArea")?.value.trim() || '',
            street: document.getElementById("editAddressStreet")?.value.trim() || '',
            buildingNumber: document.getElementById("editAddressBuilding")?.value.trim() || '',
            info: document.getElementById("editAddressInfo")?.value.trim() || '',
        };

        try {
            const res = await App.utils.ajax(route("updateAddress"), {
                method: "POST",
                body: JSON.stringify(formData),
            });

            if (res.success && res.address) {
                // Update address in list
                const index = this.customerAddresses.findIndex(a => a.addressId === addressId);
                if (index !== -1) {
                    this.customerAddresses[index] = res.address;
                }
                // Also update wizard state
                wizard.customerAddresses = this.customerAddresses;
                
                // Reload addresses display
                this.loadAddresses(this.customerAddresses, wizard);

                // Close modal
                if (modalInstance) {
                    modalInstance.hide();
                }
                document.body.classList.remove('modal-open');

                App.utils.showToast("تم تعديل العنوان بنجاح", "success");
            } else {
                App.utils.showToast("فشل تعديل العنوان", "error");
            }
        } catch (err) {
            App.utils.showToast("فشل تعديل العنوان", "error");
        }
    },
};

