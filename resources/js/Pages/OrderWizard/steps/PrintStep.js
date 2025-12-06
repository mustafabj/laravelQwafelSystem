// resources/js/Pages/OrderWizard/steps/PrintStep.js

export default class PrintStep {
    constructor(wizard) {
        this.wizard = wizard;
    }

    init() {
        const result = this.wizard.state.formResult || {};
        const container = document.getElementById("orderPrintContainer");

        if (!container) return;

        container.innerHTML = "";

        const info = document.createElement("div");
        info.className = "print-info-message";

        const p = document.createElement("p");
        p.textContent = "تم حفظ الطلب بنجاح. اضغط على زر الطباعة أعلاه لطباعته.";

        info.appendChild(p);
        container.appendChild(info);
    }
}
