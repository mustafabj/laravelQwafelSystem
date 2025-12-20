export default {
    init(wizard) {
        this.wizard = wizard;
        
        // Bind print button
        const printBtn = document.getElementById('printReviewBtn');
        if (printBtn) {
            printBtn.addEventListener('click', () => this.printReview());
        }

        // Store reference for wizard to call
        App.pages.DriverParcelWizard.PrintStep = this;
    },

    updateReviewData(savedData = null) {
        // Use saved data if available, otherwise use form data
        if (savedData && savedData.driverParcel) {
            this.updateFromSavedData(savedData.driverParcel);
        } else {
            this.updateFromFormData();
        }
    },

    updateFromSavedData(data) {
        // Invoice number
        document.getElementById('reviewParcelNumber').textContent = data.parcelNumber || '-';

        // Driver information
        document.getElementById('reviewDriverName').textContent = data.driverName || '-';
        document.getElementById('reviewDriverNumber').textContent = data.driverNumber || '-';

        // Trip information
        document.getElementById('reviewTripName').textContent = data.trip?.tripName || '-';
        document.getElementById('reviewSendTo').textContent = data.sendTo || '-';
        document.getElementById('reviewTripDate').textContent = this.formatDate(data.tripDate);

        // Details
        document.getElementById('reviewOfficeName').textContent = data.office?.officeName || '-';
        document.getElementById('reviewParcelDate').textContent = this.formatDate(data.parcelDate);
        
        // Update office name in header (logo is already set from blade template using user's office)
        const officeNameEl = document.getElementById('invoiceOfficeName');
        if (data.office?.officeName && officeNameEl) {
            officeNameEl.textContent = data.office.officeName;
        }

        // Financial information
        const cost = this.formatNumber(data.cost);
        const paid = this.formatNumber(data.paid);
        const costRest = this.formatNumber(data.costRest);
        const currency = this.getCurrencyName(data.currency);
        
        document.getElementById('reviewCost').textContent = cost === '-' ? '-' : cost + ' ' + currency;
        document.getElementById('reviewPaid').textContent = paid === '-' ? '-' : paid + ' ' + currency;
        document.getElementById('reviewCostRest').textContent = costRest === '-' ? '-' : costRest + ' ' + currency;
        document.getElementById('reviewCurrency').textContent = currency;

        // Selected parcels from saved data
        this.updateParcelsListFromSaved(data.details || []);
    },

    updateFromFormData() {
        // Invoice number
        const parcelNumber = document.getElementById('parcelNumber')?.value || '-';
        document.getElementById('reviewParcelNumber').textContent = parcelNumber;

        // Driver information
        const driverName = document.getElementById('driverName')?.value || '-';
        const driverNumber = document.getElementById('driverNumber')?.value || '-';
        document.getElementById('reviewDriverName').textContent = driverName;
        document.getElementById('reviewDriverNumber').textContent = driverNumber;

        // Trip information
        const tripSelect = document.getElementById('tripId');
        const tripName = tripSelect?.options[tripSelect.selectedIndex]?.text || '-';
        const sendTo = document.getElementById('sendTo')?.value || '-';
        const tripDate = document.getElementById('tripDate')?.value || '-';
        document.getElementById('reviewTripName').textContent = tripName;
        document.getElementById('reviewSendTo').textContent = sendTo;
        document.getElementById('reviewTripDate').textContent = this.formatDate(tripDate);

        // Details
        const officeSelect = document.getElementById('officeId');
        const officeName = officeSelect?.options[officeSelect.selectedIndex]?.text || '-';
        document.getElementById('reviewOfficeName').textContent = officeName;
        document.getElementById('reviewParcelDate').textContent = this.formatDate(new Date().toISOString().split('T')[0]);
        
        // Update office logo and name in header
        const officeLogoEl = document.getElementById('invoiceOfficeLogo');
        const officeNameEl = document.getElementById('invoiceOfficeName');
        if (officeName && officeNameEl) {
            officeNameEl.textContent = officeName;
        }

        // Financial information
        const cost = document.getElementById('cost')?.value || '0';
        const paid = document.getElementById('paid')?.value || '0';
        const costRest = document.getElementById('costRest')?.value || '0';
        const currencySelect = document.getElementById('currency');
        const currency = currencySelect?.options[currencySelect.selectedIndex]?.text || '-';
        
        const formattedCost = this.formatNumber(cost);
        const formattedPaid = this.formatNumber(paid);
        const formattedCostRest = this.formatNumber(costRest);
        
        document.getElementById('reviewCost').textContent = formattedCost === '-' ? '-' : formattedCost + ' ' + currency;
        document.getElementById('reviewPaid').textContent = formattedPaid === '-' ? '-' : formattedPaid + ' ' + currency;
        document.getElementById('reviewCostRest').textContent = formattedCostRest === '-' ? '-' : formattedCostRest + ' ' + currency;
        document.getElementById('reviewCurrency').textContent = currency;

        // Selected parcels
        this.updateParcelsList();
    },

    updateParcelsList() {
        const parcelsList = document.getElementById('reviewParcelsList');
        if (!parcelsList) return;

        const ParcelsStep = App.pages.DriverParcelWizard?.ParcelsStep;
        if (!ParcelsStep || !ParcelsStep.hasParcels()) {
            parcelsList.innerHTML = '<p class="text-muted">لا توجد إرساليات مختارة</p>';
            return;
        }

        const selected = ParcelsStep.getSelected();
        if (!selected || selected.length === 0) {
            parcelsList.innerHTML = '<p class="text-muted">لا توجد إرساليات مختارة</p>';
            return;
        }

        // Get full parcel data from selectedParcels map
        const parcelsData = Array.from(ParcelsStep.selectedParcels.values());
        this.renderParcelsTable(parcelsData, parcelsList);
    },

    updateParcelsListFromSaved(details) {
        const parcelsList = document.getElementById('reviewParcelsList');
        if (!parcelsList) return;

        if (!details || details.length === 0) {
            parcelsList.innerHTML = '<p class="text-muted">لا توجد إرساليات مختارة</p>';
            return;
        }

        const parcelsData = details.map(detail => ({
            parcelNumber: detail.parcelDetail?.parcel?.parcelNumber || '-',
            customerName: detail.parcelDetail?.parcel?.customer ? 
                `${detail.parcelDetail.parcel.customer.FName || ''} ${detail.parcelDetail.parcel.customer.LName || ''}`.trim() || '-' : '-',
            description: detail.detailInfo || '-',
            quantityTaken: detail.quantityTaken || 0,
        }));

        this.renderParcelsTable(parcelsData, parcelsList);
    },

    renderParcelsTable(parcelsData, container) {
        let html = '<table class="invoice-items-table">';
        html += '<thead><tr><th>رقم الإرسالية</th><th>اسم العميل</th><th>الوصف</th><th>الكمية</th></tr></thead>';
        html += '<tbody>';

        parcelsData.forEach(parcel => {
            html += '<tr>';
            html += `<td>${parcel.parcelNumber || '-'}</td>`;
            html += `<td>${parcel.customerName || '-'}</td>`;
            html += `<td>${parcel.description || '-'}</td>`;
            html += `<td>${parcel.quantityTaken || 0}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table>';

        container.innerHTML = html;
    },

    getCurrencyName(currencyCode) {
        const currencies = {
            'IQD': 'دينار عراقي',
            'USD': 'دولار',
            'EUR': 'يورو',
        };
        return currencies[currencyCode] || currencyCode;
    },

    formatDate(dateString) {
        if (!dateString || dateString === '-') return '-';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('ar-EG', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (e) {
            return dateString;
        }
    },

    formatNumber(value) {
        if (value === null || value === undefined || value === '' || value === '-') return '-';
        const num = parseFloat(value);
        if (isNaN(num) || num === 0) return '-';
        return num.toLocaleString('ar-EG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    printReview() {
        // Get the saved driver parcel ID and redirect to print page
        const driverParcelId = this.wizard.savedDriverParcelId;
        if (driverParcelId) {
            let printUrl;
            if (typeof route !== 'undefined') {
                printUrl = route('driver-parcels.print', driverParcelId);
            } else {
                // Fallback if route helper is not available
                printUrl = `/driver-parcels/${driverParcelId}/print`;
            }
            window.open(printUrl, '_blank');
        } else {
            const { toast } = App.utils || {};
            if (toast) {
                toast('لم يتم العثور على رقم الإرسالية', 'error');
            } else {
                alert('لم يتم العثور على رقم الإرسالية');
            }
        }
    }
};
