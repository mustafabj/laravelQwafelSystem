/**
 * BaseWizard
 * A reusable base class for wizard functionality
 * Can be extended or used as a mixin for different wizard implementations
 */
class BaseWizard {
    constructor(config = {}) {
        this.currentStep = 0;
        this.steps = null;
        this.tabsHead = null;
        this.initialized = false;
        this.config = {
            stepsContainer: config.stepsContainer || '#tabs-content',
            tabsContainer: config.tabsContainer || '.tabs ul li',
            progressBar: config.progressBar || '#wizardProgressFill',
            currentStepEl: config.currentStepEl || '#currentStepNumber',
            totalStepsEl: config.totalStepsEl || '#totalSteps',
            ...config
        };
    }

    init() {
        if (this.initialized) {
            if (App.config?.debug) {
                console.log(`[${this.constructor.name}] Already initialized, skipping`);
            }
            return;
        }

        const stepsContainer = document.querySelector(this.config.stepsContainer);
        if (!stepsContainer) {
            if (App.config?.debug) {
                console.warn(`[${this.constructor.name}] Steps container not found: ${this.config.stepsContainer}`);
            }
            return;
        }

        this.steps = stepsContainer.querySelectorAll('.tab');
        this.tabsHead = document.querySelectorAll(this.config.tabsContainer);

        if (this.steps.length === 0) {
            if (App.config?.debug) {
                console.warn(`[${this.constructor.name}] No steps found`);
            }
            return;
        }

        if (App.config?.debug) {
            console.log(`[${this.constructor.name}] Wizard initialized with ${this.steps.length} steps`);
        }

        // Initialize first step
        this.showStep(0);
        this.updateTabs(0);
        this.updateProgress();

        this.bindNavButtons();
        this.onInit();

        this.initialized = true;
    }

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
    }

    showStep(step) {
        if (!this.steps || step < 0 || step >= this.steps.length) {
            return;
        }

        this.steps.forEach((s) => s.classList.remove('active'));
        if (this.steps[step]) {
            this.steps[step].classList.add('active');
        }
        
        this.currentStep = step;
        this.updateProgress();
        this.updateTabs(step);
        this.onStepChange(step);
    }

    updateTabs(tab) {
        if (!this.tabsHead) {
            return;
        }
        this.tabsHead.forEach((t) => t.classList.remove('active'));
        if (this.tabsHead[tab]) {
            this.tabsHead[tab].classList.add('active');
        }
    }

    updateProgress() {
        if (!this.steps) {
            return;
        }

        const totalSteps = this.steps.length;
        const currentStepNum = this.currentStep + 1;
        const progress = ((currentStepNum / totalSteps) * 100).toFixed(2);

        // Update progress bar
        const progressFill = document.querySelector(this.config.progressBar);
        if (progressFill) {
            progressFill.style.width = `${progress}%`;
        }

        // Update step numbers
        const currentStepEl = document.querySelector(this.config.currentStepEl);
        const totalStepsEl = document.querySelector(this.config.totalStepsEl);
        if (currentStepEl) {
            currentStepEl.textContent = currentStepNum;
        }
        if (totalStepsEl) {
            totalStepsEl.textContent = totalSteps;
        }
    }

    nextStep() {
        if (this.canGoNext()) {
            this.currentStep++;
            if (this.currentStep >= this.steps.length) {
                this.currentStep = this.steps.length - 1;
            } else {
                this.showStep(this.currentStep);
            }
        }
    }

    prevStep() {
        if (this.canGoPrev()) {
            this.currentStep--;
            if (this.currentStep < 0) {
                this.currentStep = 0;
            } else {
                this.showStep(this.currentStep);
            }
        }
    }

    canGoNext() {
        // Override in subclasses to add validation
        return true;
    }

    canGoPrev() {
        // Override in subclasses to add validation
        return true;
    }

    onInit() {
        // Override in subclasses for initialization logic
    }

    onStepChange(step) {
        // Override in subclasses for step change logic
    }
}

// Export for use in other modules
if (typeof App !== 'undefined') {
    App.BaseWizard = BaseWizard;
}

export default BaseWizard;

