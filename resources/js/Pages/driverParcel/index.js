import BaseWizard from '../../core/BaseWizard.js';

import DriverStep from './steps/DriverStep.js';
import TripStep from './steps/TripStep.js';
import InfoStep from './steps/InfoStep.js';
import ParcelsStep from './steps/ParcelsStep.js';

import { Network, toast } from '../../core/utils.js';
class DriverParcelWizard extends BaseWizard {
    constructor() {
        super({
            stepsContainer: '#driver-parcel-tabs-content',
            tabsContainer: '.driver-parcel-tabs ul li',
            progressBar: '#driverParcelProgressFill',
            currentStepEl: '#driverParcelCurrentStep',
            totalStepsEl: '#driverParcelTotalSteps',
        });

        this.selectedDriver = null;
        this.selectedTrip = null;
        this.form = null;
    }

    init() {
        super.init();
        this.form = document.getElementById('driverParcelForm');
        if (!this.form) return;
        DriverStep.init(this);
        TripStep.init(this);
        InfoStep.init(this);
        ParcelsStep.init(this);
        // Attach ParcelsStep to App for access in canGoNext and submit
        App.pages.DriverParcelWizard.ParcelsStep = ParcelsStep;
        this.bindSubmit();
    }

    canGoNext() {
        switch (this.currentStep) {
            case 0:
                if (!this.selectedDriver) {
                    toast('يرجى اختيار السائق', 'warning');
                    return false;
                }
                break;
            case 1:
                if (!document.getElementById('tripId')?.value) {
                    toast('يرجى اختيار الرحلة', 'warning');
                    return false;
                }
                if (!document.getElementById('tripDate')?.value) {
                    toast('يرجى اختيار تاريخ الرحلة', 'warning');
                    return false;
                }
                break;
            case 3:
                if (!App.pages.DriverParcelWizard.ParcelsStep.hasParcels()) {
                    toast('يرجى إضافة إرسالية واحدة على الأقل', 'warning');
                    return false;
                }
        }
        return true;
    }

    validateAll() {
        // Validate driver
        if (!this.selectedDriver) {
            toast('يرجى اختيار السائق', 'warning');
            return false;
        }

        // Validate trip
        const tripId = document.getElementById('tripId')?.value;
        if (!tripId) {
            toast('يرجى اختيار الرحلة', 'warning');
            return false;
        }

        const tripDate = document.getElementById('tripDate')?.value;
        if (!tripDate) {
            toast('يرجى اختيار تاريخ الرحلة', 'warning');
            return false;
        }

        // Validate parcels
        if (!App.pages.DriverParcelWizard.ParcelsStep.hasParcels()) {
            toast('يرجى إضافة إرسالية واحدة على الأقل', 'warning');
            return false;
        }

        return true;
    }

    bindSubmit() {
        document.getElementById('driverParcelSubmitBtn')
            ?.addEventListener('click', e => {
                e.preventDefault();
                if (this.validateAll()) {
                    this.submit();
                }
            });
    }

    async submit() {
        try {
            const data = Object.fromEntries(new FormData(this.form));
            data.parcelDetails =
                App.pages.DriverParcelWizard.ParcelsStep.getSelected();

            const res = await Network.post(this.form.action, data);
            toast(res.message || 'تم الحفظ', 'success');
            location.href = '/driver-parcels';
        } catch (error) {
            // Error is already shown by Network.post via toast
            // Don't redirect on error
        }
    }
}

App.pages.DriverParcelWizard = new DriverParcelWizard();
export default App.pages.DriverParcelWizard;
