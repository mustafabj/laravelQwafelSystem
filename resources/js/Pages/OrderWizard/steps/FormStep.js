// resources/js/Pages/OrderWizard/steps/FormStep.js

import Network from "../../../core/Network.js";

export default class FormStep {
    constructor(wizard) {
        this.wizard = wizard;
        this.isSubmitting = false;
    }

    init() {
        const type = this.wizard.state.orderType || "parcel";

        if (type === "parcel") {
            this.initParcelForm();
        } else {
            this.initTicketForm();
        }
    }

    initParcelForm() {
        const form = document.getElementById("saveParcel");
        if (!form) return;

        const nameS = document.getElementById("nameS");
        const phoneS = document.getElementById("phoneS");
        const date = document.getElementById("date");

        const c = this.wizard.state.selectedCustomer;
        const p = this.wizard.state.selectedPhone;

        if (nameS && c) {
            nameS.value = `${c.fName || ""} ${c.lName || ""}`.trim();
        }
        if (phoneS && p) {
            phoneS.value = p.number || "";
        }
        if (date) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, "0");
            const dd = String(today.getDate()).padStart(2, "0");
            date.value = `${yyyy}-${mm}-${dd}`;
        }

        const paymentSelect = document.getElementById("paymentPaid");
        const paymentPks = document.getElementById("paymentPks");

        if (paymentSelect && paymentPks) {
            paymentSelect.addEventListener("change", () => {
                const value = paymentSelect.value;
                if (value === "unpaid" || value === "LaterPaid") {
                    paymentPks.classList.remove("hidden");
                } else {
                    paymentPks.classList.add("hidden");
                }
            });
        }

        const packageQunt = document.getElementById("packagequnt");
        const packagesDet = document.getElementById("packagesDet");
        const addPackageBtn = document.getElementById("addPackageBtn");

        if (packageQunt && packagesDet) {
            packageQunt.addEventListener("change", () => {
                this.updatePackageItems(parseInt(packageQunt.value || "1", 10));
            });
        }

        if (addPackageBtn && packageQunt && packagesDet) {
            addPackageBtn.addEventListener("click", () => {
                const current = packagesDet.querySelectorAll(".package-detail").length;
                packageQunt.value = String(current + 1);
                this.updatePackageItems(current + 1);
            });
        }

        packagesDet
            .querySelectorAll(".btn-delete-package")
            .forEach((btn) => {
                btn.addEventListener("click", () => {
                    this.handleDeletePackage(btn);
                });
            });

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.submit();
        });
    }

    initTicketForm() {
        const form = document.getElementById("saveTicket");
        if (!form) return;

        const c = this.wizard.state.selectedCustomer;
        const p = this.wizard.state.selectedPhone;
        const addr = this.wizard.state.selectedAddress;

        const namec = document.getElementById("namec");
        const phonec = document.getElementById("phonec");
        const namecp = document.getElementById("namecp");
        const datec = document.getElementById("datec");
        const addressCust = document.getElementById("addressCust");

        if (namec && c) {
            namec.value = `${c.fName || ""} ${c.lName || ""}`.trim();
        }
        if (phonec && p) {
            phonec.value = p.number || "";
        }
        if (namecp && c && c.passport) {
            namecp.value = c.passport;
        }
        if (datec) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, "0");
            const dd = String(today.getDate()).padStart(2, "0");
            datec.value = `${yyyy}-${mm}-${dd}`;
        }
        if (addressCust && addr) {
            if (addr.info === "من المكتب") {
                addressCust.value = "من المكتب";
            } else {
                const parts = [
                    addr.city,
                    addr.area,
                    addr.street,
                    addr.buildingNumber
                ].filter(Boolean);
                addressCust.value =
                    parts.join(" - ") + (addr.info ? ` (${addr.info})` : "");
            }
        }

        const paymentStatus = document.getElementById("paymentStatus");
        const paymentAmount = document.getElementById("paymentAmount");

        if (paymentStatus && paymentAmount) {
            paymentStatus.addEventListener("change", () => {
                if (paymentStatus.value === "unpaid") {
                    paymentAmount.classList.remove("hidden");
                } else {
                    paymentAmount.classList.add("hidden");
                }
            });
        }

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.submit();
        });
    }

    updatePackageItems(count) {
        const packagesDet = document.getElementById("packagesDet");
        if (!packagesDet) return;

        if (count < 1) count = 1;

        const items = packagesDet.querySelectorAll(".package-detail");
        const current = items.length;

        if (count > current) {
            const template = document.getElementById("package-container-template");
            if (!template) return;

            for (let i = current + 1; i <= count; i++) {
                const fragment = template.content.cloneNode(true);
                const item = fragment.querySelector(".package-detail");
                if (!item) continue;

                this.setupPackageItem(item, i);
                packagesDet.appendChild(fragment);
            }
        } else if (count < current) {
            for (let i = current - 1; i >= count; i--) {
                const item = packagesDet.querySelector(
                    `.package-detail[data-package-index="${i + 1}"]`
                );
                if (item) item.remove();
            }
        }

        this.renumberPackages();
    }

    setupPackageItem(item, index) {
        item.dataset.packageIndex = String(index);

        const numberEl = item.querySelector(".package-number");
        const qunInput = item.querySelector(".qun-input");
        const descInput = item.querySelector(".desc-input");
        const deleteBtn = item.querySelector(".btn-delete-package");

        if (numberEl) numberEl.textContent = `الصنف ${index}`;

        if (qunInput) {
            qunInput.id = `qun${index}`;
            qunInput.value = "1";
        }

        if (descInput) {
            descInput.id = `desc${index}`;
            descInput.value = "";
        }

        if (deleteBtn) {
            deleteBtn.addEventListener("click", () => this.handleDeletePackage(deleteBtn));
        }
    }

    renumberPackages() {
        const packagesDet = document.getElementById("packagesDet");
        if (!packagesDet) return;

        const items = packagesDet.querySelectorAll(".package-detail");

        items.forEach((item, idx) => {
            const index = idx + 1;
            item.dataset.packageIndex = String(index);

            const numberEl = item.querySelector(".package-number");
            const qunInput = item.querySelector(".qun-input");
            const descInput = item.querySelector(".desc-input");

            if (numberEl) numberEl.textContent = `الصنف ${index}`;

            if (qunInput) qunInput.id = `qun${index}`;
            if (descInput) descInput.id = `desc${index}`;
        });
    }

    handleDeletePackage(button) {
        const packagesDet = document.getElementById("packagesDet");
        if (!packagesDet) return;

        const items = packagesDet.querySelectorAll(".package-detail");
        if (items.length <= 1) {
            return;
        }

        const detail = button.closest(".package-detail");
        if (detail) detail.remove();

        const packageQunt = document.getElementById("packagequnt");
        if (packageQunt) {
            const remaining = packagesDet.querySelectorAll(".package-detail").length;
            packageQunt.value = String(remaining);
        }

        this.renumberPackages();
    }

    async submit() {
        if (this.isSubmitting) return;

        const type = this.wizard.state.orderType || "parcel";

        if (type === "parcel") {
            return this.submitParcel();
        }

        return this.submitTicket();
    }

    async submitParcel() {
        const form = document.getElementById("saveParcel");
        if (!form) return { success: false };

        const c = this.wizard.state.selectedCustomer;
        const p = this.wizard.state.selectedPhone;
        const addr = this.wizard.state.selectedAddress;

        if (!c || !c.id || !p || !addr) {
            return { success: false };
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return { success: false };
        }

        this.isSubmitting = true;

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : "";

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = "جاري الحفظ...";
        }

        try {
            const formData = new FormData(form);
            const packageDetails = [];

            const qunInputs = form.querySelectorAll('input[name="qun[]"]');
            const descInputs = form.querySelectorAll('textarea[name="desc[]"]');

            qunInputs.forEach((qunInput, index) => {
                const qun = parseInt(qunInput.value || "1", 10);
                const desc = descInputs[index].value || "";
                if (qun > 0) {
                    packageDetails.push({ qun, desc });
                }
            });

            const payload = {
                parcelNumber: Number(formData.get("parcelid")) || 0,
                customerId: c.id,
                recipientName: formData.get("nameST") || "",
                recipientNumber: formData.get("phoneST") || "",
                sendTo: formData.get("addressST") || "",
                cost: parseFloat(formData.get("cost") || "0") || 0,
                currency: formData.get("currency") || "JD",
                paid: formData.get("paid") || "paid",
                paidMethod: formData.get("paidMethod") || "cash",
                costRest: parseFloat(formData.get("costRest") || "0") || 0,
                officeReId: Number(formData.get("officeST")) || 0,
                paidInMainOffice: formData.get("paidInMainOffice") === "on",
                packageDetails
            };

            const res = await Network.post(route("storeParcel"), payload);

            if (res && res.success) {
                this.wizard.state.formResult = res;
                return res;
            }

            return { success: false };
        } catch (err) {
            console.error("Failed to submit parcel", err);
            return { success: false };
        } finally {
            this.isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText || "حفظ";
            }
        }
    }

    async submitTicket() {
        const form = document.getElementById("saveTicket");
        if (!form) return { success: false };

        const c = this.wizard.state.selectedCustomer;
        const p = this.wizard.state.selectedPhone;
        const addr = this.wizard.state.selectedAddress;

        if (!c || !c.id || !p || !addr) {
            return { success: false };
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return { success: false };
        }

        this.isSubmitting = true;

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : "";

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = "جاري الحفظ...";
        }

        try {
            const formData = new FormData(form);

            const payload = {
                ticketNumber: Number(formData.get("ticketId")) || 0,
                customerId: c.id,
                destination: formData.get("TrancustTo") || "",
                Seat: formData.get("custbn") || "",
                travelDate: formData.get("datact") || "",
                travelTime: formData.get("timect") || "",
                cost: parseFloat(formData.get("cost") || "0") || 0,
                currency: formData.get("currency") || "JD",
                paid: formData.get("paid") || "paid",
                costRest: parseFloat(formData.get("costRest") || "0") || 0,
                addressId: addr.addressId || 0
            };

            const res = await Network.post(route("storeTicket"), payload);

            if (res && res.success) {
                this.wizard.state.formResult = res;
                return res;
            }

            return { success: false };
        } catch (err) {
            console.error("Failed to submit ticket", err);
            return { success: false };
        } finally {
            this.isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText || "حفظ";
            }
        }
    }
}
