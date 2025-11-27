App.pages = App.pages || {};

App.pages.OrderWizard = App.pages.OrderWizard || {};

App.pages.OrderWizard.FormStep = {
    async loadForm(type, wizard) {
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
            
            newParcelBtn.addEventListener("click", async (e) => {
                e.preventDefault();
                e.stopPropagation();
                wizard.currentType = "parcel";
                await this.loadForm("parcel", wizard);
                wizard.nextStep();
            });
        }

        if (ticketBtn) {
            // Clone and replace to remove old listeners
            const newTicketBtn = ticketBtn.cloneNode(true);
            ticketBtn.parentNode.replaceChild(newTicketBtn, ticketBtn);
            
            newTicketBtn.addEventListener("click", async (e) => {
                e.preventDefault();
                e.stopPropagation();
                wizard.currentType = "ticket";
                await this.loadForm("ticket", wizard);
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
        } else if (type === 'ticket') {
            this.bindTicketEvents();
        }
    },

    bindParcelEvents() {
        // Package quantity change
        const packageQunt = document.getElementById('packagequnt');
        if (packageQunt) {
            packageQunt.addEventListener('change', (e) => {
                this.updatePackageItems(parseInt(e.target.value));
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

        const currentItems = container.querySelectorAll('.package-detail').length;
        
        if (count > currentItems) {
            // Add new items
            for (let i = currentItems + 1; i <= count; i++) {
                const item = this.createPackageItem(i);
                container.appendChild(item);
            }
        } else if (count < currentItems) {
            // Remove excess items
            const items = container.querySelectorAll('.package-detail');
            for (let i = items.length - 1; i >= count; i--) {
                items[i].remove();
            }
        }

        // Update package numbers
        container.querySelectorAll('.package-detail').forEach((item, index) => {
            const numberEl = item.querySelector('.package-number');
            if (numberEl) {
                numberEl.textContent = `الصنف ${index + 1}`;
            }
        });
    },

    createPackageItem(index) {
        const div = document.createElement('div');
        div.className = 'package-detail';
        div.setAttribute('data-package-index', index);
        div.innerHTML = `
            <h4 class="package-number">الصنف ${index}</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="qun${index}">العدد</label>
                    <input type="number" name="qun[]" id="qun${index}" class="qun-input" min="1" value="1">
                </div>
                <div class="form-group">
                    <label for="desc${index}">الوصف</label>
                    <textarea name="desc[]" id="desc${index}" class="desc-input" rows="3"></textarea>
                </div>
            </div>
            <button type="button" class="btn-delete-package" onclick="deletePackage(this)">حذف</button>
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
        
        if (container && packageQunt) {
            packageDetail.remove();
            const remainingCount = container.querySelectorAll('.package-detail').length;
            packageQunt.value = remainingCount;
            
            // Update package numbers
            container.querySelectorAll('.package-detail').forEach((item, index) => {
                const numberEl = item.querySelector('.package-number');
                if (numberEl) {
                    numberEl.textContent = `الصنف ${index + 1}`;
                }
            });
        }
    }
};

