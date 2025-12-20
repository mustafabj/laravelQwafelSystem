import BaseWizard from '../../core/BaseWizard.js';

import DriverStep from './steps/DriverStep.js';
import TripStep from './steps/TripStep.js';
import InfoStep from './steps/InfoStep.js';
import ParcelsStep from './steps/ParcelsStep.js';
import PrintStep from './steps/PrintStep.js';

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
        PrintStep.init(this);
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
                break;
            case 4:
                // Print step - can only be accessed after save, prevent manual navigation
                return false;
        }
        return true;
    }

    onStepChange(step) {
        // Update review data when entering print step
        if (step === 4 && App.pages.DriverParcelWizard.PrintStep) {
            // Use saved data if available
            if (this.savedDriverParcelData) {
                App.pages.DriverParcelWizard.PrintStep.updateReviewData(this.savedDriverParcelData);
            } else {
                App.pages.DriverParcelWizard.PrintStep.updateReviewData();
            }
        }
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
            
            if (res.success) {
                toast(res.message || 'تم الحفظ', 'success');
                
                // Store the saved driver parcel data for the print step
                if (res.driverParcelId) {
                    this.savedDriverParcelId = res.driverParcelId;
                }
                
                // Navigate to print step after successful save
                setTimeout(() => {
                    this.currentStep = 4;
                    this.showStep(4);
                }, 500);
            }
        } catch (error) {
            // Error is already shown by Network.post via toast
            // Don't redirect on error
        }
    }
}

App.pages.DriverParcelWizard = new DriverParcelWizard();
export default App.pages.DriverParcelWizard;
