/**
 * DriverParcelWizard
 * Main wizard controller for creating driver parcels
 * Uses BaseWizard and step modules
 */
import BaseWizard from './BaseWizard.js';

class DriverParcelWizard extends BaseWizard {
    constructor() {
        super({
            stepsContainer: '#driver-parcel-tabs-content',
            tabsContainer: '.driver-parcel-tabs ul li',
            progressBar: '#driverParcelProgressFill',
            currentStepEl: '#driverParcelCurrentStep',
            totalStepsEl: '#driverParcelTotalSteps',
        });

        // Preserve existing step modules if they were loaded first
        const existingSteps = App.pages?.DriverParcelWizard || {};
        Object.assign(this, existingSteps);

        this.selectedDriver = null;
        this.selectedTrip = null;
        this.form = null;
    }

    init() {
        super.init();
        
        this.form = document.getElementById('driverParcelForm');
        if (!this.form) {
            return;
        }

        this.bindSubmitButton();
        this.onInit();
    }

    onInit() {
        // Initialize step modules
        if (App.pages.DriverParcelWizard.DriverStep) {
            App.pages.DriverParcelWizard.DriverStep.bindDriverSearch();
        }
        if (App.pages.DriverParcelWizard.TripStep) {
            App.pages.DriverParcelWizard.TripStep.bindTripSelection(this);
        }
        if (App.pages.DriverParcelWizard.InfoStep) {
            App.pages.DriverParcelWizard.InfoStep.bindFinancialCalculations();
        }
            if (App.pages.DriverParcelWizard.ParcelsStep) {
                App.pages.DriverParcelWizard.ParcelsStep.init(this);
            }
    }

    onStepChange(step) {
        // Handle step-specific logic
        if (step === 3 && App.pages.DriverParcelWizard.ParcelsStep) {
            // Ensure parcels step is initialized
            if (!App.pages.DriverParcelWizard.ParcelsStep.initialized) {
                App.pages.DriverParcelWizard.ParcelsStep.init(this);
            }
            // Update summary when entering parcels step
            App.pages.DriverParcelWizard.ParcelsStep.updateSummary();
        }
    }

    canGoNext() {
        // Validate current step before proceeding
        switch (this.currentStep) {
            case 0: // Driver step
                if (!this.selectedDriver) {
                    App.utils.showToast('يرجى اختيار السائق', 'warning');
                    return false;
                }
                return true;
            case 1: // Trip step
                const tripSelect = document.getElementById('tripId');
                const tripDate = document.getElementById('tripDate');
                if (!tripSelect || !tripSelect.value) {
                    App.utils.showToast('يرجى اختيار الرحلة', 'warning');
                    return false;
                }
                if (!tripDate || !tripDate.value) {
                    App.utils.showToast('يرجى اختيار تاريخ الرحلة', 'warning');
                    return false;
                }
                return true;
            case 2: // Info step
                // Basic validation
                const driverName = document.getElementById('driverName');
                const driverNumber = document.getElementById('driverNumber');
                if (!driverName || !driverName.value) {
                    App.utils.showToast('اسم السائق مطلوب', 'warning');
                    return false;
                }
                if (!driverNumber || !driverNumber.value) {
                    App.utils.showToast('رقم السائق مطلوب', 'warning');
                    return false;
                }
                return true;
            case 3: // Parcels step
                if (!App.pages.DriverParcelWizard.ParcelsStep || 
                    App.pages.DriverParcelWizard.ParcelsStep.selectedParcels.size === 0) {
                    App.utils.showToast('يرجى إضافة إرسالية واحدة على الأقل', 'warning');
                    return false;
                }
                return true;
            default:
                return true;
        }
    }

    canGoPrev() {
        // Allow going back from any step
        return true;
    }

    bindSubmitButton() {
        const submitBtn = document.getElementById('driverParcelSubmitBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.canGoNext()) {
                    this.submitForm();
                }
            });
        }
    }

    async submitForm() {
        if (!this.form) return;

        const submitBtn = document.getElementById('driverParcelSubmitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

        try {
            const formData = new FormData(this.form);
            
            // Get selected parcels
            const parcelsStep = App.pages.DriverParcelWizard.ParcelsStep;
            const parcelDetails = parcelsStep ? parcelsStep.getSelectedParcels() : [];

            // Build data object
            const data = {
                parcelNumber: formData.get('parcelNumber'),
                tripId: formData.get('tripId'),
                tripDate: formData.get('tripDate'),
                driverName: formData.get('driverName'),
                driverNumber: formData.get('driverNumber'),
                driverId: formData.get('driverId'),
                sendTo: formData.get('sendTo'),
                officeId: formData.get('officeId') || formData.get('hiddenOfficeId'),
                cost: formData.get('cost') || 0,
                paid: formData.get('paid') || 0,
                costRest: formData.get('costRest') || 0,
                currency: formData.get('currency') || 'IQD',
                parcelDetails: parcelDetails,
            };

            const response = await fetch(this.form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                App.utils.showToast(result.message || 'تم حفظ الإرسالية بنجاح', 'success');
                setTimeout(() => {
                    window.location.href = '/driver-parcels';
                }, 1500);
            } else {
                App.utils.showToast(result.message || 'حدث خطأ أثناء الحفظ', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            App.utils.showToast('حدث خطأ أثناء الحفظ', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
}

// Export and attach to App
if (typeof App !== 'undefined') {
    if (!App.pages) {
        App.pages = {};
    }
    App.pages.DriverParcelWizard = new DriverParcelWizard();
}

export default App.pages.DriverParcelWizard;

