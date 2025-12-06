// resources/js/Pages/OrderWizard/steps/CustomerStep.js

import TemplateManager from "../../../core/Template.js";
import Network from "../../../core/Network.js";
import { Modal } from "bootstrap";

export default class CustomerStep {
    constructor(wizard) {
        this.wizard = wizard;

        this.searchTimeout = null;
        this.debounceDelay = 300;

        this.searchController = null;
    }

    init() {
        this.input = document.getElementById("search-customer");
        this.tbody = document.getElementById("customerBody");
        this.addCustomerBtn = document.getElementById("addCustomer");

        if (this.input) {
            this.input.addEventListener("input", () => this.handleSearch());
            this.input.addEventListener("keyup", () => this.handleSearch());
        }

        if (this.addCustomerBtn) {
            this.addCustomerBtn.addEventListener("click", () => this.openCustomerModal());
        }

        if (this.tbody) {
            this.tbody.addEventListener("click", (e) => {
                const row = e.target.closest("tr[data-customer-id]");
                if (!row) return;

                if (e.target.closest("button")) return;

                const id = parseInt(row.dataset.customerId, 10);
                if (Number.isNaN(id)) return;

                this.selectCustomer(id);
            });
        }
    }

    handleSearch() {
        if (!this.input || !this.tbody) return;

        const query = this.input.value.trim();

        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        if (this.searchController) {
            this.searchController.abort();
        }

        if (query.length < 2) {
            this.renderEmptyState("initial");
            return;
        }

        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, this.debounceDelay);
    }

    async performSearch(query) {
        if (!this.tbody) return;

        this.searchController = new AbortController();

        try {
            const res = await Network.post(route("getCustomers"), {
                search: query
            });

            if (res.customers && Array.isArray(res.customers) && res.customers.length > 0) {
                this.renderCustomerRows(res.customers);
            } else if (res.html) {
                this.tbody.innerHTML = res.html;
            } else {
                this.renderEmptyState("no-results");
            }
        } catch (err) {
            if (err.name !== "AbortError") {
                this.renderEmptyState("no-results");
            }
        } finally {
            this.searchController = null;
        }
    }

    renderEmptyState(type = "initial") {
        if (!this.tbody) return;

        const fragment = TemplateManager.load("customer-empty-template");
        const root = fragment.firstElementChild;

        if (root) {
            root.dataset.type = type;
        }

        this.tbody.innerHTML = "";
        this.tbody.appendChild(fragment);
    }

    renderCustomerRows(customers) {
        if (!this.tbody) return;

        this.tbody.innerHTML = "";

        customers.forEach((cust) => {
            const row = TemplateManager.cloneRoot("customer-row-template");
            if (!row) return;

            row.dataset.customerId = cust.customerId;

            const nameCell = row.querySelector("[data-field='name']");
            const passportCell = row.querySelector("[data-field='passport']");
            const phone1Cell = row.querySelector("[data-field='phone1']");
            const phone2Cell = row.querySelector("[data-field='phone2']");
            const phone3Cell = row.querySelector("[data-field='phone3']");
            const phone4Cell = row.querySelector("[data-field='phone4']");
            const stateCell = row.querySelector("[data-field='state']");

            if (nameCell) {
                nameCell.textContent = `${cust.FName || ""} ${cust.LName || ""}`.trim();
            }
            if (passportCell) passportCell.textContent = cust.customerPassport || "";
            if (phone1Cell) phone1Cell.textContent = cust.phone1 || "";
            if (phone2Cell) phone2Cell.textContent = cust.phone2 || "";
            if (phone3Cell) phone3Cell.textContent = cust.phone3 || "";
            if (phone4Cell) phone4Cell.textContent = cust.phone4 || "";
            if (stateCell) stateCell.textContent = cust.customerState || "";

            this.tbody.appendChild(row);
        });
    }

    async selectCustomer(customerId) {
        try {
            const res = await Network.post(route("getCustomer"), {
                customerId
            });

            this.wizard.state.selectedCustomer = {
                id: res.customerId,
                fName: res.FName,
                lName: res.LName,
                passport: res.customerPassport || ""
            };

            this.wizard.state.customerPhones = [
                res.phone1,
                res.phone2,
                res.phone3,
                res.phone4
            ].filter(Boolean);

            this.wizard.state.customerAddresses = res.addresses || [];

            const fnameInput = document.getElementById("fname");
            const lnameInput = document.getElementById("lname");

            if (fnameInput) fnameInput.value = res.FName || "";
            if (lnameInput) lnameInput.value = res.LName || "";

            this.wizard.goTo("phone");
        } catch (err) {
            console.error("Failed to load customer", err);
        }
    }

    openCustomerModal() {
        const modalFragment = TemplateManager.load("customer-modal-template");
        const modalRoot = modalFragment.firstElementChild;
        if (!modalRoot) return;

        let modalHost = document.getElementById("dynamicModalHost");
        if (!modalHost) {
            modalHost = document.createElement("div");
            modalHost.id = "dynamicModalHost";
            document.body.appendChild(modalHost);
        }

        modalHost.innerHTML = "";
        modalHost.appendChild(modalFragment);

        const modalElement = modalRoot;
        const instance = new Modal(modalElement);
        instance.show();

        const cancelBtn = modalElement.querySelector("#cancelCustomerBtn");
        const closeBtn = modalElement.querySelector("#closeCustomerModal");
        const submitBtn = modalElement.querySelector("#submitCustomerBtn");
        const form = modalElement.querySelector("#addCustomerForm");

        const closeModal = () => {
            instance.hide();
        };

        if (cancelBtn) cancelBtn.addEventListener("click", closeModal);
        if (closeBtn) closeBtn.addEventListener("click", closeModal);

        if (submitBtn && form) {
            submitBtn.addEventListener("click", async () => {
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());

                try {
                    const res = await Network.post(route("storeCustomer"), payload);

                    if (res && res.success && res.customer) {
                        this.wizard.state.selectedCustomer = {
                            id: res.customer.customerId,
                            fName: res.customer.FName,
                            lName: res.customer.LName,
                            passport: res.customer.customerPassport || ""
                        };

                        this.wizard.state.customerPhones = [
                            res.customer.phone1,
                            res.customer.phone2,
                            res.customer.phone3,
                            res.customer.phone4
                        ].filter(Boolean);

                        this.wizard.state.customerAddresses = res.addresses || [];

                        const fnameInput = document.getElementById("fname");
                        const lnameInput = document.getElementById("lname");

                        if (fnameInput) fnameInput.value = res.customer.FName || "";
                        if (lnameInput) lnameInput.value = res.customer.LName || "";

                        closeModal();
                        this.wizard.goTo("phone");
                    }
                } catch (err) {
                    console.error("Failed to store customer", err);
                }
            });
        }
    }
}
