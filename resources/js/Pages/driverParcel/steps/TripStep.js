export default {
    init() {
        const trip = document.getElementById('tripId');
        const sendTo = document.getElementById('sendTo');
        const office = document.getElementById('officeId');
        const hiddenOffice = document.getElementById('hiddenOfficeId');
        const tripDate = document.getElementById('tripDate');
    
        // Set minimum date to today
        if (tripDate) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const todayFormatted = `${yyyy}-${mm}-${dd}`;
            
            tripDate.min = todayFormatted;
            
            // Set default value to today if empty
            if (!tripDate.value) {
                tripDate.value = todayFormatted;
            }
            
            // Add validation to prevent past dates
            tripDate.addEventListener('change', (e) => {
                const selectedDate = new Date(e.target.value);
                const minDate = new Date(todayFormatted);
                
                if (selectedDate < minDate) {
                    if (App.utils?.showToast) {
                        App.utils.showToast('لا يمكن اختيار تاريخ سابق لليوم', 'warning');
                    }
                    e.target.value = todayFormatted;
                }
            });
        }
    
        if (!trip) return;
    
        trip.addEventListener('change', e => {
            const opt = e.target.selectedOptions[0];
            if (!opt) return;
    
            if (sendTo) sendTo.value = opt.dataset.destination || '';
            if (office) {
                office.value = opt.dataset.officeId || '';
                office.disabled = true;
            }
            if (hiddenOffice) hiddenOffice.value = opt.dataset.officeId || '';
        });
    }
    
};
