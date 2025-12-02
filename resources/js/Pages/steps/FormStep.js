App.pages = App.pages || {};

App.pages.OrderWizard = App.pages.OrderWizard || {};

App.pages.OrderWizard.FormStep = {
    // Cache for pre-loaded forms
    cachedForms: {
        parcel: null,
        ticket: null
    },
    formsLoaded: false,

    // Pre-load both forms when type step is shown
    async preloadForms() {
        if (this.formsLoaded) return;

        try {
            // Load both forms in parallel
            const [parcelRes, ticketRes] = await Promise.all([
                App.utils.ajax(route("wizard.parcel.form"), { method: "GET" }),
                App.utils.ajax(route("wizard.ticket.form"), { method: "GET" })
            ]);

            if (parcelRes.html && parcelRes.html.trim() !== "") {
                this.cachedForms.parcel = parcelRes.html;
            }
            if (ticketRes.html && ticketRes.html.trim() !== "") {
                this.cachedForms.ticket = ticketRes.html;
            }

            this.formsLoaded = true;
            if (App.config?.debug) {
                console.log('[FormStep] Forms pre-loaded successfully');
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[FormStep] Failed to pre-load forms:', err);
            }
        }
    },

    // Instant form loading from cache
    loadForm(type, wizard) {
        const container = document.getElementById("orderStepFormContainer");
        if (!container) return;

        // Check if form is cached
        const cachedForm = this.cachedForms[type];
        if (cachedForm) {
            // Instant swap - no loading delay
            container.innerHTML = cachedForm;
            // Populate readonly fields and bind form events
            this.populateFormFields(type, wizard);
            this.bindFormEvents(type, wizard);
            return;
        }

        // Fallback: load if not cached (shouldn't happen if preload worked)
        this.loadFormAsync(type, wizard);
    },

    // Async loading (fallback)
    async loadFormAsync(type, wizard) {
        const container = document.getElementById("orderStepFormContainer");
        if (!container) return;

        // Show loading state
        await this.showLoadingState(container);

        try {
            const url =
                type === "parcel"
                    ? route("wizard.parcel.form")
                    : route("wizard.ticket.form");

            const res = await App.utils.ajax(url, { method: "GET" });
            
            if (res.html && res.html.trim() !== "") {
                // Cache it for next time
                this.cachedForms[type] = res.html;
                
                requestAnimationFrame(() => {
                    container.innerHTML = res.html;
                    // Populate readonly fields and bind form events
                    this.populateFormFields(type, wizard);
                    this.bindFormEvents(type, wizard);
                });
            } else {
                await this.showErrorState(container);
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[FormStep] Failed to load form:', err);
            }
            await this.showErrorState(container);
        }
    },

    async showLoadingState(container) {
        try {
            const res = await App.utils.ajax(route("getFormLoading"), {
                method: "POST",
            });

            if (res.html) {
                container.innerHTML = res.html;
            }
        } catch (err) {
            // Fallback loading message
            container.innerHTML = '<div class="loading-state"><p>جاري تحميل النموذج...</p></div>';
        }
    },

    async showErrorState(container) {
        try {
            const res = await App.utils.ajax(route("getFormError"), {
                method: "POST",
            });

            if (res.html) {
                container.innerHTML = res.html;
            }
        } catch (err) {
            // Fallback error message
            container.innerHTML = '<div class="error-state"><p>خطأ في تحميل النموذج</p><button class="btn btn-primary" onclick="location.reload()">إعادة المحاولة</button></div>';
        }
    },

    async submitCurrentForm(wizard) {
        const type = wizard.currentType;
        if (!type) {
            App.utils.showToast("الرجاء اختيار نوع الطلب أولاً", "warning");
            return;
        }

        const form = document.querySelector(
            type === "parcel" ? "#saveParcel" : "#saveTicket"
        );
        
        if (!form) {
            App.utils.showToast("النموذج غير موجود", "error");
            return;
        }

        // Validate form before submission
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = Object.fromEntries(new FormData(form).entries());

        try {
            const url =
                type === "parcel"
                    ? route("wizard.parcel.save")
                    : route("wizard.ticket.save");

            const res = await App.utils.ajax(url, {
                method: "POST",
                body: JSON.stringify(formData),
            });

            // res should contain HTML for print view, example: { html: '...' }
            const printContainer = document.getElementById(
                "orderPrintContainer"
            );
            
            if (printContainer && res.html) {
                requestAnimationFrame(() => {
                    printContainer.innerHTML = res.html;
                });
            }

            App.utils.showToast("تم حفظ الطلب بنجاح", "success");
            wizard.nextStep();
        } catch (err) {
            if (App.config?.debug) {
                console.error('[FormStep] Failed to submit form:', err);
            }
            App.utils.showToast("حدث خطأ أثناء الحفظ", "error");
        }
    },

    bindTypeButtons(wizard) {
        // Remove existing listeners to prevent duplicates
        const parcelBtn = document.querySelector('[data-order-type="parcel"]');
        const ticketBtn = document.querySelector('[data-order-type="ticket"]');
        const submitBtn = document.getElementById("wizardSubmitBtn");

        if (parcelBtn) {
            // Clone and replace to remove old listeners
            const newParcelBtn = parcelBtn.cloneNode(true);
            parcelBtn.parentNode.replaceChild(newParcelBtn, parcelBtn);
            
            newParcelBtn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                wizard.currentType = "parcel";
                // Instant load from cache
                this.loadForm("parcel", wizard);
                wizard.nextStep();
            });
        }

        if (ticketBtn) {
            // Clone and replace to remove old listeners
            const newTicketBtn = ticketBtn.cloneNode(true);
            ticketBtn.parentNode.replaceChild(newTicketBtn, ticketBtn);
            
            newTicketBtn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                wizard.currentType = "ticket";
                // Instant load from cache
                this.loadForm("ticket", wizard);
                wizard.nextStep();
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener("click", async (e) => {
                e.preventDefault();
                await this.submitCurrentForm(wizard);
            });
        }
    },

    populateFormFields(type, wizard) {
        if (type === 'parcel') {
            // Populate sender info (readonly)
            const nameS = document.getElementById('nameS');
            const phoneS = document.getElementById('phoneS');
            const date = document.getElementById('date');
            
            if (nameS && wizard.selectedCustomer) {
                nameS.value = `${wizard.selectedCustomer.fName || ''} ${wizard.selectedCustomer.lName || ''}`.trim();
            }
            if (phoneS && wizard.selectedPhone) {
                phoneS.value = wizard.selectedPhone.number || '';
            }
            if (date) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                date.value = `${yyyy}-${mm}-${dd}`;
            }
        } else if (type === 'ticket') {
            // Populate traveler info (readonly)
            const namec = document.getElementById('namec');
            const phonec = document.getElementById('phonec');
            const namecp = document.getElementById('namecp');
            const datec = document.getElementById('datec');
            const addressCust = document.getElementById('addressCust');
            
            if (namec && wizard.selectedCustomer) {
                namec.value = `${wizard.selectedCustomer.fName || ''} ${wizard.selectedCustomer.lName || ''}`.trim();
            }
            if (phonec && wizard.selectedPhone) {
                phonec.value = wizard.selectedPhone.number || '';
            }
            if (namecp) {
                namecp.value = ''; // Passport number - not available from customer
            }
            if (datec) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                datec.value = `${yyyy}-${mm}-${dd}`;
            }
            if (addressCust && wizard.selectedAddress) {
                const addr = wizard.selectedAddress;
                if (addr.info === 'من المكتب') {
                    addressCust.value = 'من المكتب';
                } else {
                    const parts = [addr.city, addr.area, addr.street, addr.buildingNumber].filter(Boolean);
                    addressCust.value = parts.join(' - ') + (addr.info ? ` (${addr.info})` : '');
                }
            }
        }
    },

    bindFormEvents(type, wizard) {
        if (type === 'parcel') {
            this.bindParcelEvents();
            // Ensure delete buttons are properly shown/hidden on initial load
            setTimeout(() => {
                this.updatePackageNumbers();
            }, 100);
        } else if (type === 'ticket') {
            this.bindTicketEvents();
        }
    },

    bindParcelEvents() {
        // Package quantity change
        const packageQunt = document.getElementById('packagequnt');
        if (packageQunt) {
            packageQunt.addEventListener('change', (e) => {
                let count = parseInt(e.target.value);
                // Ensure minimum of 1
                if (count < 1) {
                    count = 1;
                    e.target.value = 1;
                }
                this.updatePackageItems(count);
            });
        }

        // Add package button
        const addPackageBtn = document.getElementById('addPackageBtn');
        if (addPackageBtn) {
            addPackageBtn.addEventListener('click', () => {
                const container = document.getElementById('packagesDet');
                const packageQunt = document.getElementById('packagequnt');
                if (container && packageQunt) {
                    const currentCount = container.querySelectorAll('.package-detail').length;
                    const newIndex = currentCount + 1;
                    const item = this.createPackageItem(newIndex);
                    container.appendChild(item);
                    
                    // Update select box value
                    if (newIndex <= 10) {
                        packageQunt.value = newIndex;
                    }
                    
                    // Update package numbers
                    this.updatePackageNumbers();
                }
            });
        }

        // Payment status change - show/hide costRest
        const paymentPaid = document.getElementById('paymentPaid');
        if (paymentPaid) {
            paymentPaid.addEventListener('change', (e) => {
                const paymentPks = document.getElementById('paymentPks');
                if (paymentPks) {
                    if (e.target.value === 'unpaid' || e.target.value === 'LaterPaid') {
                        paymentPks.classList.remove('hidden');
                    } else {
                        paymentPks.classList.add('hidden');
                    }
                }
            });
        }

        // Form submission
        const form = document.getElementById('saveParcel');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const wizard = App.pages.OrderWizard;
                await this.submitCurrentForm(wizard);
            });
        }
    },

    bindTicketEvents() {
        // Payment status change - show/hide costRest
        const paymentStatus = document.getElementById('paymentStatus');
        if (paymentStatus) {
            paymentStatus.addEventListener('change', (e) => {
                const paymentAmount = document.getElementById('paymentAmount');
                if (paymentAmount) {
                    if (e.target.value === 'unpaid') {
                        paymentAmount.classList.remove('hidden');
                    } else {
                        paymentAmount.classList.add('hidden');
                    }
                }
            });
        }

        // Form submission
        const form = document.getElementById('saveTicket');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const wizard = App.pages.OrderWizard;
                await this.submitCurrentForm(wizard);
            });
        }
    },

    updatePackageItems(count) {
        const container = document.getElementById('packagesDet');
        if (!container) return;

        // Ensure minimum of 1 package
        if (count < 1) {
            count = 1;
            const packageQunt = document.getElementById('packagequnt');
            if (packageQunt) {
                packageQunt.value = 1;
            }
        }

        const currentItems = container.querySelectorAll('.package-detail').length;
        
        if (count > currentItems) {
            // Add new items
            for (let i = currentItems + 1; i <= count; i++) {
                const item = this.createPackageItem(i);
                container.appendChild(item);
            }
        } else if (count < currentItems) {
            // Remove excess items, but keep at least 1
            const items = container.querySelectorAll('.package-detail');
            const itemsToRemove = currentItems - count;
            for (let i = items.length - 1; i >= count && i >= 1; i--) {
                items[i].remove();
            }
        }

        // Update package numbers
        this.updatePackageNumbers();
    },

    updatePackageNumbers() {
        const container = document.getElementById('packagesDet');
        if (!container) return;
        
        const totalPackages = container.querySelectorAll('.package-detail').length;
        
        container.querySelectorAll('.package-detail').forEach((item, index) => {
            const numberEl = item.querySelector('.package-number');
            if (numberEl) {
                numberEl.textContent = `الصنف ${index + 1}`;
            }
            
            // Update data attribute
            item.setAttribute('data-package-index', index + 1);
            
            // Update input IDs and labels
            const qunInput = item.querySelector('.qun-input');
            const descInput = item.querySelector('.desc-input');
            const newIndex = index + 1;
            
            if (qunInput) {
                qunInput.id = `qun${newIndex}`;
                const label = item.querySelector('label[for^="qun"]');
                if (label) label.setAttribute('for', `qun${newIndex}`);
            }
            
            if (descInput) {
                descInput.id = `desc${newIndex}`;
                const label = item.querySelector('label[for^="desc"]');
                if (label) label.setAttribute('for', `desc${newIndex}`);
            }
            
            // Hide delete button if only one package remains
            const deleteBtn = item.querySelector('.btn-delete-package');
            if (deleteBtn) {
                if (totalPackages <= 1) {
                    deleteBtn.style.display = 'none';
                } else {
                    deleteBtn.style.display = 'flex';
                }
            }
        });
    },

    createPackageItem(index) {
        const div = document.createElement('div');
        div.className = 'package-detail';
        div.setAttribute('data-package-index', index);
        div.innerHTML = `
            <div class="package-header">
                <h4 class="package-number">الصنف ${index}</h4>
            </div>
            <div class="package-content">
                <div class="form-group">
                    <label for="qun${index}">
                        <span class="label-icon">🔢</span>
                        العدد
                    </label>
                    <input type="number" name="qun[]" id="qun${index}" class="qun-input" min="1" value="1" placeholder="1">
                </div>
                <div class="form-group form-group-full">
                    <label for="desc${index}">
                        <span class="label-icon">📝</span>
                        الوصف
                    </label>
                    <textarea name="desc[]" id="desc${index}" class="desc-input" rows="4" placeholder="أدخل وصف الصنف..."></textarea>
                </div>
            </div>
            <button type="button" class="btn-delete-package" onclick="deletePackage(this)">
                <span class="btn-icon">🗑️</span>
                حذف
            </button>
        `;
        return div;
    },
};

// Global function for package deletion (called from onclick)
window.deletePackage = function(button) {
    const packageDetail = button.closest('.package-detail');
    if (packageDetail) {
        const container = document.getElementById('packagesDet');
        const packageQunt = document.getElementById('packagequnt');
        const formStep = App.pages?.OrderWizard?.FormStep;
        
        if (container && packageQunt) {
            // Check if there's only one package left - prevent deletion
            const currentCount = container.querySelectorAll('.package-detail').length;
            if (currentCount <= 1) {
                if (App.utils && App.utils.showToast) {
                    App.utils.showToast('يجب أن يكون هناك على الأقل صنف واحد', 'warning');
                } else {
                    alert('يجب أن يكون هناك على الأقل صنف واحد');
                }
                return;
            }
            
            packageDetail.remove();
            const remainingCount = container.querySelectorAll('.package-detail').length;
            packageQunt.value = remainingCount;
            
            // Update package numbers using the method
            if (formStep && typeof formStep.updatePackageNumbers === 'function') {
                formStep.updatePackageNumbers();
            } else {
                // Fallback: manual update
                container.querySelectorAll('.package-detail').forEach((item, index) => {
                    const numberEl = item.querySelector('.package-number');
                    if (numberEl) {
                        numberEl.textContent = `الصنف ${index + 1}`;
                    }
                });
            }
        }
    }
};

