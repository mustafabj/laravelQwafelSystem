// resources/js/Pages/OrderWizard/steps/AddressStep.js

import TemplateManager from "../../../core/Template.js";
import Network from "../../../core/Network.js";
import { Modal } from "bootstrap";

export default class AddressStep {
    constructor(wizard) {
        this.wizard = wizard;
    }

    init() {
        this.tbody = document.getElementById("addressBody");
        this.addBtn = document.getElementById("addAddressBtn");
        this.officeBtn = document.getElementById("officeAddressBtn");

        this.renderAddresses();

        if (this.tbody) {
            this.tbody.addEventListener("click", (e) => {
                const editBtn = e.target.closest(".btn-edit-address");
                if (editBtn) {
                    const id = parseInt(editBtn.dataset.addressId, 10);
                    if (!Number.isNaN(id)) {
                        this.openEditModal(id);
                    }
                    e.stopPropagation();
                    return;
                }

                const row = e.target.closest("tr[data-address-id]");
                if (!row) return;

                const addressId = parseInt(row.dataset.addressId, 10);
                if (Number.isNaN(addressId)) return;

                this.selectAddressById(addressId);
            });
        }

        if (this.addBtn) {
            this.addBtn.addEventListener("click", () => this.openAddModal());
        }

        if (this.officeBtn) {
            this.officeBtn.addEventListener("click", () => this.selectOfficeAddress());
        }
    }

    renderAddresses() {
        if (!this.tbody) return;

        const addresses = this.wizard.state.customerAddresses || [];

        this.tbody.innerHTML = "";

        if (addresses.length === 0) {
            const empty = TemplateManager.load("address-empty-template");
            this.tbody.appendChild(empty);
            return;
        }

        addresses.forEach((addr) => {
            const row = TemplateManager.cloneRoot("address-row-template");
            if (!row) return;

            row.dataset.addressId = String(addr.addressId);

            const city = row.querySelector("[data-field='city']");
            const area = row.querySelector("[data-field='area']");
            const street = row.querySelector("[data-field='street']");
            const building = row.querySelector("[data-field='buildingNumber']");
            const info = row.querySelector("[data-field='info']");
            const editBtn = row.querySelector(".btn-edit-address");

            if (city) city.textContent = addr.city || "";
            if (area) area.textContent = addr.area || "";
            if (street) street.textContent = addr.street || "";
            if (building) building.textContent = addr.buildingNumber || "";
            if (info) info.textContent = addr.info || "";
            if (editBtn) {
                editBtn.dataset.addressId = String(addr.addressId);
            }

            this.tbody.appendChild(row);
        });
    }

    selectAddressById(addressId) {
        const addresses = this.wizard.state.customerAddresses || [];
        const address = addresses.find((a) => a.addressId === addressId);

        if (!address) {
            return;
        }

        this.wizard.state.selectedAddress = { ...address };

        if (this.tbody) {
            this.tbody
                .querySelectorAll("tr[data-address-id]")
                .forEach((row) => row.classList.remove("selected"));

            const activeRow = this.tbody.querySelector(
                `tr[data-address-id="${addressId}"]`
            );
            if (activeRow) {
                activeRow.classList.add("selected");
            }
        }

        this.wizard.goTo("type");
    }

    selectOfficeAddress() {
        this.wizard.state.selectedAddress = {
            addressId: 1,
            info: "من المكتب"
        };

        this.wizard.goTo("type");
    }

    openAddModal() {
        this.openModal("add");
    }

    openEditModal(addressId) {
        const addresses = this.wizard.state.customerAddresses || [];
        const address = addresses.find((a) => a.addressId === addressId);
        if (!address) return;

        this.openModal("edit", address);
    }

    openModal(mode, address = null) {
        const fragment = TemplateManager.load("address-modal-template");
        const modalRoot = fragment.firstElementChild;
        if (!modalRoot) return;

        let host = document.getElementById("dynamicModalHost");
        if (!host) {
            host = document.createElement("div");
            host.id = "dynamicModalHost";
            document.body.appendChild(host);
        }

        host.innerHTML = "";
        host.appendChild(fragment);

        const modalElement = modalRoot;
        const instance = new Modal(modalElement);
        instance.show();

        const title = modalElement.querySelector("#addressModalTitle");
        const cityInput = modalElement.querySelector("#addressCity");
        const areaInput = modalElement.querySelector("#addressArea");
        const streetInput = modalElement.querySelector("#addressStreet");
        const buildingInput = modalElement.querySelector("#addressBuilding");
        const infoInput = modalElement.querySelector("#addressInfo");
        const cancelBtn = modalElement.querySelector("#cancelAddressBtn");
        const closeBtn = modalElement.querySelector("#closeAddressModal");
        const submitBtn = modalElement.querySelector("#submitAddressBtn");
        const form = modalElement.querySelector("#addressForm");

        if (title) {
            title.textContent = mode === "edit" ? "تعديل عنوان" : "اضافة عنوان";
        }

        if (mode === "edit" && address) {
            if (cityInput) cityInput.value = address.city || "";
            if (areaInput) areaInput.value = address.area || "";
            if (streetInput) streetInput.value = address.street || "";
            if (buildingInput) buildingInput.value = address.buildingNumber || "";
            if (infoInput) infoInput.value = address.info || "";
        }

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

                let res;
                try {
                    if (mode === "edit" && address) {
                        res = await Network.post(route("updateAddress"), {
                            addressId: address.addressId,
                            ...payload
                        });
                    } else {
                        const cust = this.wizard.state.selectedCustomer;
                        if (!cust || !cust.id) return;

                        res = await Network.post(route("storeAddress"), {
                            customerId: cust.id,
                            ...payload
                        });
                    }
                } catch (err) {
                    console.error("Failed to save address", err);
                    return;
                }

                if (res && res.addresses) {
                    this.wizard.state.customerAddresses = res.addresses;
                } else if (res && res.address) {
                    const addresses = this.wizard.state.customerAddresses || [];
                    const existingIndex = addresses.findIndex(
                        (a) => a.addressId === res.address.addressId
                    );
                    if (existingIndex !== -1) {
                        addresses[existingIndex] = res.address;
                    } else {
                        addresses.push(res.address);
                    }
                    this.wizard.state.customerAddresses = addresses;
                }

                this.renderAddresses();
                closeModal();
            });
        }
    }
}
