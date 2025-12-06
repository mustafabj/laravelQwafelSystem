// resources/js/Pages/OrderWizard/steps/PhoneStep.js

import TemplateManager from "../../../core/Template.js";
import Network from "../../../core/Network.js";

export default class PhoneStep {
    constructor(wizard) {
        this.wizard = wizard;
        this.maxPhones = 5;
        this.saveTimeout = null;
        this.saveDebounceDelay = 800;
    }

    init() {
        this.container = document.getElementById("phoneNumbersContainer");
        this.addBtn = document.getElementById("addPhoneBtn");

        const fnameInput = document.getElementById("fname");
        const lnameInput = document.getElementById("lname");

        if (this.wizard.state.selectedCustomer) {
            const c = this.wizard.state.selectedCustomer;
            if (fnameInput) fnameInput.value = c.fName || "";
            if (lnameInput) lnameInput.value = c.lName || "";
        }

        if (!this.container) return;

        this.container.innerHTML = "";
        this.phoneCounter = 1;

        const phones = this.wizard.state.customerPhones;
        if (phones && phones.length > 0) {
            phones.forEach((p) => this.addPhoneItem(p));
        } else {
            this.addPhoneItem("");
        }

        if (this.addBtn) {
            this.addBtn.addEventListener("click", () => this.addPhoneItem(""));
        }

        this.container.addEventListener("click", (e) => {
            const item = e.target.closest(".phone-item");
            if (!item) return;

            const actionSelect = e.target.closest(".btn-select-phone");
            const actionDelete = e.target.closest(".btn-delete-phone");

            if (actionSelect) {
                this.handleSelect(item);
            } else if (actionDelete) {
                this.handleDelete(item);
            }
        });

        this.container.addEventListener("input", () => {
            this.debouncedSavePhones();
        });
    }

    addPhoneItem(phoneNumber) {
        if (!this.container) return;

        if (this.phoneCounter > this.maxPhones) {
            return;
        }

        const item = TemplateManager.cloneRoot("phone-item-template");
        if (!item) return;

        const index = this.phoneCounter;

        item.dataset.phoneIndex = index.toString();

        const label = item.querySelector("label");
        const input = item.querySelector(".phone-input");

        if (label) {
            label.setAttribute("for", `phone${index}`);
            label.textContent = `رقم الهاتف ${index}`;
        }

        if (input) {
            input.id = `phone${index}`;
            input.dataset.phoneId = index.toString();
            input.value = phoneNumber || "";
        }

        this.container.appendChild(item);
        this.phoneCounter += 1;
    }

    getPhones() {
        if (!this.container) return [];

        const inputs = Array.from(
            this.container.querySelectorAll(".phone-input")
        );

        return inputs
            .map((inp) => inp.value.trim())
            .filter((v) => v.length > 0);
    }

    debouncedSavePhones() {
        if (!this.wizard.state.selectedCustomer) return;

        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }

        this.saveTimeout = setTimeout(() => {
            this.savePhones();
        }, this.saveDebounceDelay);
    }

    async savePhones() {
        const cust = this.wizard.state.selectedCustomer;
        if (!cust || !cust.id) return;

        const phones = this.getPhones();
        if (phones.length === 0) return;

        try {
            await Network.post(route("updateCustomerPhones"), {
                customerId: cust.id,
                phones: phones.slice(0, 4)
            });

            this.wizard.state.customerPhones = phones;
        } catch (err) {
            console.error("Failed to save phones", err);
        }
    }

    handleSelect(phoneItem) {
        const input = phoneItem.querySelector(".phone-input");
        if (!input) return;

        const value = input.value.trim();
        if (value.length < 2) {
            return;
        }

        this.wizard.state.selectedPhone = {
            number: value,
            phoneId: input.dataset.phoneId
        };

        document
            .querySelectorAll(".phone-item")
            .forEach((it) => it.classList.remove("selected"));
        phoneItem.classList.add("selected");

        this.wizard.goTo("address");

        this.savePhones();
    }

    async handleDelete(phoneItem) {
        if (!this.container) return;

        const items = this.container.querySelectorAll(".phone-item");
        if (items.length <= 1) {
            return;
        }

        phoneItem.remove();

        const remaining = this.container.querySelectorAll(".phone-item");
        remaining.forEach((item, index) => {
            const label = item.querySelector("label");
            const input = item.querySelector(".phone-input");
            const number = index + 1;

            item.dataset.phoneIndex = number.toString();

            if (label) {
                label.setAttribute("for", `phone${number}`);
                label.textContent = `رقم الهاتف ${number}`;
            }

            if (input) {
                input.id = `phone${number}`;
                input.dataset.phoneId = number.toString();
            }
        });

        this.phoneCounter = remaining.length + 1;

        await this.savePhones();
    }
}
