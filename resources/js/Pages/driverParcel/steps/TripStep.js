export default {
    init() {
        const trip = document.getElementById('tripId');
        const sendTo = document.getElementById('sendTo');
        const office = document.getElementById('officeId');
        const hiddenOffice = document.getElementById('hiddenOfficeId');
    
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
