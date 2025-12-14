/**
 * TripStep Module
 * Handles trip selection step in DriverParcelWizard
 */
App.pages = App.pages || {};
App.pages.DriverParcelWizard = App.pages.DriverParcelWizard || {};

App.pages.DriverParcelWizard.TripStep = {
    bindTripSelection(wizard) {
        const tripSelect = document.getElementById('tripId');
        const tripDateInput = document.getElementById('tripDate');
        const sendToInput = document.getElementById('sendTo');
        const officeIdInput = document.getElementById('officeId');
        const hiddenOfficeIdInput = document.getElementById('hiddenOfficeId');

        if (!tripSelect) return;

        tripSelect.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            if (selectedOption.value) {
                const destination = selectedOption.dataset.destination || '';
                const officeId = selectedOption.dataset.officeId || '';

                if (sendToInput) sendToInput.value = destination;
                if (officeIdInput) {
                    officeIdInput.value = officeId;
                    officeIdInput.disabled = true;
                }
                if (hiddenOfficeIdInput) hiddenOfficeIdInput.value = officeId;

                wizard.selectedTrip = {
                    tripId: selectedOption.value,
                    tripName: selectedOption.text,
                    destination: destination,
                    officeId: officeId
                };
                
                // Update summary if parcels step is initialized
                if (App.pages.DriverParcelWizard.ParcelsStep && App.pages.DriverParcelWizard.ParcelsStep.updateSummary) {
                    App.pages.DriverParcelWizard.ParcelsStep.updateSummary();
                }
            }
        });

        // Set minimum date to today
        if (tripDateInput) {
            const today = new Date().toISOString().split('T')[0];
            tripDateInput.min = today;
            if (!tripDateInput.value) {
                tripDateInput.value = today;
            }
            
            // Update summary when trip date changes
            tripDateInput.addEventListener('change', () => {
                if (App.pages.DriverParcelWizard.ParcelsStep && App.pages.DriverParcelWizard.ParcelsStep.updateSummary) {
                    App.pages.DriverParcelWizard.ParcelsStep.updateSummary();
                }
            });
        }
    }
};

