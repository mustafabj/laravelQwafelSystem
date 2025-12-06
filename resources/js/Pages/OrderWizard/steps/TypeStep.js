// resources/js/Pages/OrderWizard/steps/TypeStep.js

export default class TypeStep {
    constructor(wizard) {
        this.wizard = wizard;
    }

    init() {
        const cards = document.querySelectorAll("[data-order-type]");
        cards.forEach((card) => {
            card.addEventListener("click", () => {
                const type = card.dataset.orderType;
                if (!type) return;

                this.wizard.state.orderType = type; // "parcel" or "ticket"
                this.wizard.goTo("form");
            });
        });
    }
}
