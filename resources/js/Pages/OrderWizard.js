App.pages = App.pages || {};

// Preserve existing step modules if they were loaded first
const existingSteps = App.pages.OrderWizard || {};

App.pages.OrderWizard = {
    ...existingSteps, // Preserve step modules (FormStep, CustomerStep, PhoneStep)
    currentStep: 0,
    steps: null,
    tabsHead: null,
    currentType: null, // 'parcel' or 'ticket'
    selectedCustomer: null,
    selectedPhone: null,
    selectedAddress: null,
    customerAddresses: [],
    phoneCounter: 1,
    initialized: false,

    init() {
        if (this.initialized) {
            if (App.config.debug) {
                console.log('[OrderWizard] Already initialized, skipping');
            }
            return;
        }

        const form = document.getElementById("tabs-content");
        if (!form) return;

        this.steps = form.querySelectorAll(".tab");
        this.tabsHead = document.querySelectorAll(".tabs ul li");

        if (this.steps.length === 0) return;

        if(App.config.debug){
            console.log("[OrderWizard] Stepper initialized");
        }

        // Initialize first step
        this.showStep(0);
        this.tabshead(0);
        this.updateProgress();

        this.bindNavButtons();
        this.bindSubmitButton();
        this.bindPrintButton();
        this.initDateTimeDefaults();
        
        // Initialize step modules (check if they exist)
        if (App.pages.OrderWizard.FormStep) {
            App.pages.OrderWizard.FormStep.bindTypeButtons(this);
        }
        if (App.pages.OrderWizard.CustomerStep) {
            App.pages.OrderWizard.CustomerStep.bindSearchCustomer();
        }
        if (App.pages.OrderWizard.PhoneStep) {
            App.pages.OrderWizard.PhoneStep.bindPhoneStep(this);
        }
        if (App.pages.OrderWizard.AddressStep) {
            App.pages.OrderWizard.AddressStep.bindAddressStep(this);
        }
        
        this.initialized = true;
    },

    bindNavButtons() {
        // Next buttons
        document.querySelectorAll("[data-wizard-next]").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                this.nextStep();
            });
        });

        // Previous buttons
        document.querySelectorAll("[data-wizard-prev]").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                this.prevStep();
            });
        });
    },

    bindSubmitButton() {
        const submitBtn = document.getElementById('wizardSubmitBtn');
        if (submitBtn) {
            // Clone and replace to remove old listeners
            const newSubmitBtn = submitBtn.cloneNode(true);
            submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
            
            newSubmitBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                // Check if we're on the form step (step 4, index 4)
                if (this.currentStep === 4) {
                    await this.submitCurrentForm();
                } else {
                    this.nextStep();
                }
            });
        }
    },

    bindPrintButton() {
        const printBtn = document.getElementById('wizardPrintBtn');
        if (printBtn) {
            // Clone and replace to remove old listeners
            const newPrintBtn = printBtn.cloneNode(true);
            printBtn.parentNode.replaceChild(newPrintBtn, printBtn);
            
            newPrintBtn.addEventListener('click', (e) => {
                e.preventDefault();
                // Open print page in new window if URL is available
                if (this.printUrl) {
                    window.open(this.printUrl, '_blank');
                } else {
                    App.utils.showToast("لا يوجد رابط للطباعة", "warning");
                }
            });
        }
    },

    showStep(step) {
        this.steps.forEach((s) => s.classList.remove("active"));
        if (this.steps[step]) {
            this.steps[step].classList.add("active");
        }
        this.updateProgress();
        
        // Load addresses when address step is shown
        if (step === 2 && this.customerAddresses && App.pages.OrderWizard.AddressStep) {
            App.pages.OrderWizard.AddressStep.loadAddresses(this.customerAddresses, this);
            // Pre-load forms in background while user is on address step
            if (App.pages.OrderWizard.FormStep) {
                App.pages.OrderWizard.FormStep.preloadForms();
            }
        }
        
        // Bind type buttons when type step is shown
        if (step === 3 && App.pages.OrderWizard.FormStep) {
            // Ensure forms are loaded (in case user skipped address step)
            App.pages.OrderWizard.FormStep.preloadForms();
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                App.pages.OrderWizard.FormStep.bindTypeButtons(this);
            }, 100);
        }
    },

    tabshead(tab) {
        this.tabsHead.forEach((t) => t.classList.remove("active"));
        if (this.tabsHead[tab]) {
            this.tabsHead[tab].classList.add("active");
        }
    },

    updateProgress() {
        const totalSteps = this.steps.length;
        const currentStepNum = this.currentStep + 1;
        const progress = ((currentStepNum / totalSteps) * 100).toFixed(2);
        
        // Update progress bar
        const progressFill = document.getElementById("wizardProgressFill");
        if (progressFill) {
            progressFill.style.width = `${progress}%`;
        }
        
        // Update step numbers
        const currentStepNumber = document.getElementById("currentStepNumber");
        const totalStepsEl = document.getElementById("totalSteps");
        if (currentStepNumber) currentStepNumber.textContent = currentStepNum;
        if (totalStepsEl) totalStepsEl.textContent = totalSteps;
    },

    nextStep() {
        this.currentStep++;
        if (this.currentStep >= this.steps.length) {
            this.currentStep = this.steps.length - 1;
        } else {
            this.showStep(this.currentStep);
            this.tabshead(this.currentStep);
        }
    },

    prevStep() {
        if (this.currentStep === 0) {
            // same behavior as your old code: go back to home/index
            window.location.href = "/";
            return;
        }
        
        // If we're on step 5 (form) and came from address step (skipped type step)
        // Go back to address step (step 3, index 2) instead of type step (step 4, index 3)
        if (this.currentStep === 4 && this.currentType === 'ticket' && this.selectedAddress) {
            this.currentStep = 2; // Go back to address step
        } else {
            this.currentStep--;
        }
        
        this.showStep(this.currentStep);
        this.tabshead(this.currentStep);
    },

    initDateTimeDefaults() {
        // same as your old code: set min date, default time, etc.
        const dateInput = document.getElementById("datact");
        if (dateInput) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, "0");
            const dd = String(today.getDate()).padStart(2, "0");
            const todayFormatted = `${yyyy}-${mm}-${dd}`;

            dateInput.min = today.toISOString().split("T")[0];
            dateInput.value = todayFormatted;
        }

        const timeInput = document.getElementById("timect");
        if (timeInput) {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, "0");
            const min = String(now.getMinutes()).padStart(2, "0");
            timeInput.value = `${hh}:${min}`;
        }
    },

    // Delegate to CustomerStep
    async selectCustomer(id) {
        await App.pages.OrderWizard.CustomerStep.selectCustomer(id, this);
    },

    async loadCustomerData(customerId) {
        await App.pages.OrderWizard.CustomerStep.loadCustomerData(customerId, this);
    },

    // Delegate to PhoneStep
    loadPhoneNumbers(phones) {
        App.pages.OrderWizard.PhoneStep.loadPhoneNumbers(phones, this);
    },

    // Delegate to FormStep
    async loadForm(type) {
        await App.pages.OrderWizard.FormStep.loadForm(type, this);
    },

    async submitCurrentForm() {
        await App.pages.OrderWizard.FormStep.submitCurrentForm(this);
    },

    // Delegate to AddressStep
    loadAddresses(addresses) {
        if (App.pages.OrderWizard.AddressStep) {
            App.pages.OrderWizard.AddressStep.loadAddresses(addresses, this);
        }
    },
};
