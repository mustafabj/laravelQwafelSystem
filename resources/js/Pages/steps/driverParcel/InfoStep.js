/**
 * InfoStep Module
 * Handles basic and financial information step in DriverParcelWizard
 */
App.pages = App.pages || {};
App.pages.DriverParcelWizard = App.pages.DriverParcelWizard || {};

App.pages.DriverParcelWizard.InfoStep = {
    bindFinancialCalculations() {
        const costInput = document.getElementById('cost');
        const paidInput = document.getElementById('paid');
        const costRestInput = document.getElementById('costRest');
        const currencySelect = document.getElementById('currency');

        if (!costInput || !paidInput || !costRestInput) return;

        const calculateRest = () => {
            const cost = parseFloat(costInput.value) || 0;
            const paid = parseFloat(paidInput.value) || 0;
            const rest = Math.max(0, cost - paid);
            costRestInput.value = rest.toFixed(2);
            
            // Update summary if parcels step is initialized
            if (App.pages.DriverParcelWizard.ParcelsStep && App.pages.DriverParcelWizard.ParcelsStep.updateSummary) {
                App.pages.DriverParcelWizard.ParcelsStep.updateSummary();
            }
        };

        costInput.addEventListener('input', calculateRest);
        paidInput.addEventListener('input', calculateRest);
        
        if (currencySelect) {
            currencySelect.addEventListener('change', () => {
                if (App.pages.DriverParcelWizard.ParcelsStep && App.pages.DriverParcelWizard.ParcelsStep.updateSummary) {
                    App.pages.DriverParcelWizard.ParcelsStep.updateSummary();
                }
            });
        }
    }
};

