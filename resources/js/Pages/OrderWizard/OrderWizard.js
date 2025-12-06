// resources/js/Pages/OrderWizard/OrderWizard.js

import TemplateManager from "../../core/Template.js";

import CustomerStep from "./steps/CustomerStep.js";
import PhoneStep from "./steps/PhoneStep.js";
import AddressStep from "./steps/AddressStep.js";
import TypeStep from "./steps/TypeStep.js";
import FormStep from "./steps/FormStep.js";
import PrintStep from "./steps/PrintStep.js";

export default class OrderWizard {
    constructor(rootElement) {
        this.root = rootElement || document.getElementById("orderWizardRoot");
        this.container = document.getElementById("wizardStepContainer");

        this.progressFill = document.getElementById("wizardProgressFill");
        this.currentStepNumberEl = document.getElementById("currentStepNumber");
        this.totalStepsEl = document.getElementById("totalSteps");
        this.tabsHead = document.getElementById("wizardTabsHead");

        this.prevBtn = document.getElementById("wizardPrevBtn");
        this.nextBtn = document.getElementById("wizardNextBtn");
        this.submitBtn = document.getElementById("wizardSubmitBtn");
        this.printBtn = document.getElementById("wizardPrintBtn");

        // global state
        this.state = {
            current: "customer",          // current step key
            orderType: null,              // "parcel" | "ticket"

            selectedCustomer: null,
            customerPhones: [],
            selectedPhone: null,

            customerAddresses: [],
            selectedAddress: null,

            formResult: null              // { success, printUrl, ... }
        };

        this.steps = {
            customer: new CustomerStep(this),
            phone:    new PhoneStep(this),
            address:  new AddressStep(this),
            type:     new TypeStep(this),
            form:     new FormStep(this),
            print:    new PrintStep(this)
        };

        this.stepOrder = ["customer", "phone", "address", "type", "form", "print"];
    }

    init() {
        if (!this.root || !this.container) {
            return;
        }

        this.bindNavButtons();
        this.bindTabClick();

        this.totalStepsEl.textContent = this.stepOrder.length.toString();

        this.renderStep("customer");
        this.updateProgress();
        this.updateButtons();
    }

    bindNavButtons() {
        if (this.prevBtn) {
            this.prevBtn.addEventListener("click", () => this.prev());
        }

        if (this.nextBtn) {
            this.nextBtn.addEventListener("click", () => this.next());
        }

        if (this.submitBtn) {
            this.submitBtn.addEventListener("click", () => this.submit());
        }

        if (this.printBtn) {
            this.printBtn.addEventListener("click", () => this.handlePrint());
        }
    }

    bindTabClick() {
        if (!this.tabsHead) return;

        this.tabsHead.addEventListener("click", (e) => {
            const li = e.target.closest("li[data-step]");
            if (!li) return;

            const stepKey = li.dataset.step;
            if (!stepKey || !this.stepOrder.includes(stepKey)) return;

            const currentIndex = this.stepOrder.indexOf(this.state.current);
            const targetIndex = this.stepOrder.indexOf(stepKey);

            if (targetIndex <= currentIndex || targetIndex === currentIndex + 1) {
                this.goTo(stepKey);
            }
        });
    }

    renderStep(key) {
        let templateId;

        if (key === "form") {
            templateId = this.state.orderType === "ticket"
                ? "step-form-ticket-template"
                : "step-form-parcel-template";
        } else {
            templateId = `step-${key}-template`;
        }

        TemplateManager.render(templateId, this.container);

        if (this.steps[key] && typeof this.steps[key].init === "function") {
            this.steps[key].init();
        }
    }

    next() {
        const index = this.stepOrder.indexOf(this.state.current);
        if (index === -1 || index === this.stepOrder.length - 1) return;

        const nextKey = this.stepOrder[index + 1];
        this.goTo(nextKey);
    }

    prev() {
        const index = this.stepOrder.indexOf(this.state.current);
        if (index <= 0) return;

        const prevKey = this.stepOrder[index - 1];
        this.goTo(prevKey);
    }

    goTo(stepKey) {
        if (!this.stepOrder.includes(stepKey)) return;

        this.state.current = stepKey;
        this.renderStep(stepKey);
        this.updateProgress();
        this.updateButtons();
    }

    updateProgress() {
        const index = this.stepOrder.indexOf(this.state.current);
        const total = this.stepOrder.length;

        if (index === -1) return;

        const percent = ((index + 1) / total) * 100;

        if (this.progressFill) {
            this.progressFill.style.width = `${percent}%`;
        }

        if (this.currentStepNumberEl) {
            this.currentStepNumberEl.textContent = (index + 1).toString();
        }

        if (this.totalStepsEl) {
            this.totalStepsEl.textContent = total.toString();
        }

        this.updateTabsHeader();
    }

    updateTabsHeader() {
        if (!this.tabsHead) return;

        this.tabsHead.querySelectorAll("li[data-step]").forEach((li) => {
            li.classList.remove("active");
            if (li.dataset.step === this.state.current) {
                li.classList.add("active");
            }
        });
    }

    updateButtons() {
        const step = this.state.current;

        if (this.nextBtn) this.nextBtn.classList.remove("d-none");
        if (this.submitBtn) this.submitBtn.classList.add("d-none");
        if (this.printBtn) this.printBtn.classList.add("d-none");

        if (step === "form") {
            if (this.nextBtn) this.nextBtn.classList.add("d-none");
            if (this.submitBtn) this.submitBtn.classList.remove("d-none");
        }

        if (step === "print") {
            if (this.nextBtn) this.nextBtn.classList.add("d-none");
            if (this.submitBtn) this.submitBtn.classList.add("d-none");
            if (this.printBtn) this.printBtn.classList.remove("d-none");
        }
    }

    async submit() {
        if (!this.steps.form || typeof this.steps.form.submit !== "function") {
            return;
        }

        const result = await this.steps.form.submit();

        if (result && result.success) {
            this.state.formResult = result;
            this.goTo("print");
        }
    }

    handlePrint() {
        const result = this.state.formResult;
        if (!result || !result.printUrl) return;

        window.open(result.printUrl, "_blank");
    }
}
