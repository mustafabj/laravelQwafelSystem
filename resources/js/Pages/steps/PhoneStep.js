App.pages = App.pages || {};

App.pages.OrderWizard = App.pages.OrderWizard || {};

App.pages.OrderWizard.PhoneStep = {
    saveTimeout: null,
    saveDebounceDelay: 1000,
    maxPhones: 5,
    isSelecting: false,

    loadPhoneNumbers(phones, wizard) {
        const container = document.getElementById("phoneNumbersContainer");
        if (!container) return;

        container.innerHTML = "";
        wizard.phoneCounter = 1;

        // Load phones synchronously using template (fast!)
        phones.forEach((phone) => {
            if (phone && phone.trim() !== "") {
                this.addPhoneNumber(phone, wizard);
            }
        });

        // If no phones, add one empty field
        if (phones.every(p => !p || p.trim() === "")) {
            this.addPhoneNumber("", wizard);
        }

        // Save if customer is selected
        if (wizard.selectedCustomer && wizard.selectedCustomer.id) {
            this.savePhonesToDatabase(wizard);
        }
    },

    addPhoneNumber(phoneNumber = "", wizard) {
        if (wizard.phoneCounter > this.maxPhones) {
            App.utils.showToast("يمكنك إضافة 5 أرقام هاتف كحد أقصى", "warning");
            return;
        }

        const container = document.getElementById("phoneNumbersContainer");
        if (!container) return;

        // Get template from DOM (included in page load)
        const template = document.getElementById("phoneItemTemplate");
        if (!template) {
            console.error('[PhoneStep] Phone item template not found');
            return;
        }

        // Clone the template
        const phoneItem = template.content.cloneNode(true);
        const phoneItemDiv = phoneItem.querySelector('.phone-item');
        
        if (!phoneItemDiv) {
            console.error('[PhoneStep] Phone item structure not found in template');
            return;
        }

        // Update template values
        const phoneIndex = wizard.phoneCounter;
        phoneItemDiv.dataset.phoneIndex = phoneIndex;
        
        const label = phoneItemDiv.querySelector('label');
        const input = phoneItemDiv.querySelector('input');
        
        if (label) {
            label.textContent = `رقم الهاتف ${phoneIndex}`;
            label.setAttribute('for', `phone${phoneIndex}`);
        }
        
        if (input) {
            input.id = `phone${phoneIndex}`;
            input.value = phoneNumber;
            input.dataset.phoneId = phoneIndex;
        }

        // Append to container
        container.appendChild(phoneItem);
        wizard.phoneCounter++;
    },

    async savePhonesToDatabase(wizard) {
        if (!wizard.selectedCustomer || !wizard.selectedCustomer.id) return;

        try {
            const phones = this.getPhoneNumbers();
            
            if (phones.length === 0) {
                return;
            }

            await App.utils.ajax(route("updateCustomerPhones"), {
                method: "POST",
                body: JSON.stringify({
                    customerId: wizard.selectedCustomer.id,
                    phones: phones.slice(0, 4), // Max 4 phones in database
                }),
            });

            if (App.config?.debug) {
                console.log('[PhoneStep] Phones saved successfully');
            }
        } catch (err) {
            if (App.config?.debug) {
                console.error('[PhoneStep] Failed to save phones:', err);
            }
            App.utils.showToast("فشل حفظ أرقام الهاتف", "error");
        }
    },

    getPhoneNumbers() {
        const phones = [];
        const phoneInputs = document.querySelectorAll(".phone-input");
        phoneInputs.forEach((inp) => {
            const value = inp.value.trim();
            if (value) phones.push(value);
        });
        return phones;
    },

    async updatePhonesInBackground(wizard) {
        if (!wizard.selectedCustomer || !wizard.selectedCustomer.id) {
            return;
        }

        const phones = this.getPhoneNumbers();
        
        if (phones.length === 0) {
            return;
        }

        try {
            await App.utils.ajax(route("updateCustomerPhones"), {
                method: "POST",
                body: JSON.stringify({
                    customerId: wizard.selectedCustomer.id,
                    phones: phones.slice(0, 4), // Max 4 phones in database
                }),
            });
        } catch (err) {
            // Silently fail in background - user already advanced
            if (App.config?.debug) {
                console.error('[PhoneStep] Background phone update failed:', err);
            }
        }
    },

    debouncedSave(wizard) {
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }

        this.saveTimeout = setTimeout(() => {
            if (wizard.selectedCustomer && wizard.selectedCustomer.id) {
                this.savePhonesToDatabase(wizard);
            }
        }, this.saveDebounceDelay);
    },

    bindPhoneStep(wizard) {
        const container = document.getElementById("phoneNumbersContainer");
        const addBtn = document.getElementById("addPhoneBtn");
        
        if (!container) return;

        // Add phone button
        if (addBtn) {
            addBtn.addEventListener("click", () => {
                this.addPhoneNumber("", wizard);
            });
        }

        // Event delegation for phone actions
        container.addEventListener("click", (e) => {
            const button = e.target.closest("button");
            if (!button) return;

            const action = button.dataset.action;
            const phoneItem = button.closest(".phone-item");
            if (!phoneItem) return;

            if (action === "select") {
                this.selectPhone(phoneItem, wizard);
            } else if (action === "delete") {
                this.deletePhone(phoneItem, wizard);
            }
        });

        // Auto-save on input change (debounced)
        container.addEventListener("input", (e) => {
            if (e.target.classList.contains("phone-input")) {
                this.debouncedSave(wizard);
            }
        });

        // Prevent scrolling in phone inputs
        container.addEventListener("wheel", (e) => {
            if (e.target.classList.contains("phone-input")) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, { passive: false });

        container.addEventListener("scroll", (e) => {
            if (e.target.classList.contains("phone-input")) {
                e.target.scrollLeft = 0;
                e.target.scrollTop = 0;
            }
        });

        // Prevent touch scrolling on mobile
        container.addEventListener("touchmove", (e) => {
            if (e.target.classList.contains("phone-input")) {
                e.preventDefault();
            }
        }, { passive: false });
    },

    async selectPhone(phoneItem, wizard) {
        // Prevent multiple simultaneous selections
        if (this.isSelecting) {
            return;
        }

        const input = phoneItem.querySelector(".phone-input");
        if (!input) return;

        const phoneNumber = input.value.trim();

        if (phoneNumber.length < 2) {
            App.utils.showToast("الرجاء إدخال رقم هاتف صحيح", "warning");
            return;
        }

        if (!wizard.selectedCustomer || !wizard.selectedCustomer.id) {
            App.utils.showToast("الرجاء اختيار عميل أولاً", "warning");
            return;
        }

        this.isSelecting = true;

        // Disable all select buttons during processing
        const allSelectButtons = document.querySelectorAll('.btn-select-phone');
        allSelectButtons.forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
        });

        // Store selected phone immediately
        wizard.selectedPhone = {
            number: phoneNumber,
            phoneId: input.dataset.phoneId,
        };

        // Visual feedback
        this.highlightSelectedPhone(phoneItem);

        // Re-enable buttons immediately
        allSelectButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.cursor = '';
        });
        this.isSelecting = false;

        // Addresses will be loaded when address step is shown
        // They are already stored in wizard.customerAddresses

        // Advance to next step immediately
        wizard.nextStep();

        // Update customer phones in background (non-blocking)
        this.updatePhonesInBackground(wizard).catch(err => {
            if (App.config?.debug) {
                console.error('[PhoneStep] Failed to update phones in background:', err);
            }
        });
    },

    highlightSelectedPhone(selectedItem) {
        document.querySelectorAll(".phone-item").forEach((item) => {
            item.classList.remove("selected");
        });
        selectedItem.classList.add("selected");
    },

    async deletePhone(phoneItem, wizard) {
        const container = document.getElementById("phoneNumbersContainer");
        if (!container) return;

        const phoneItems = container.querySelectorAll(".phone-item");

        if (phoneItems.length <= 1) {
            App.utils.showToast("يجب أن يكون هناك رقم هاتف واحد على الأقل", "warning");
            return;
        }

        phoneItem.remove();
        
        // Re-number remaining phones
        this.renumberPhones(container, wizard);

        // Update database after deletion
        if (wizard.selectedCustomer && wizard.selectedCustomer.id) {
            try {
                await this.savePhonesToDatabase(wizard);
                App.utils.showToast("تم حذف رقم الهاتف بنجاح", "success");
            } catch (err) {
                if (App.config?.debug) {
                    console.error('[PhoneStep] Failed to delete phone:', err);
                }
                App.utils.showToast("فشل تحديث أرقام الهاتف", "error");
            }
        }
    },

    renumberPhones(container, wizard) {
        const remainingPhones = container.querySelectorAll(".phone-item");
        remainingPhones.forEach((item, index) => {
            const phoneIndex = index + 1;
            item.dataset.phoneIndex = phoneIndex;
            
            const label = item.querySelector("label");
            const input = item.querySelector("input");
            
            if (label) {
                label.textContent = `رقم الهاتف ${phoneIndex}`;
                label.setAttribute("for", `phone${phoneIndex}`);
            }
            
            if (input) {
                input.id = `phone${phoneIndex}`;
                input.dataset.phoneId = phoneIndex;
            }
        });

        wizard.phoneCounter = remainingPhones.length + 1;
    },
};

