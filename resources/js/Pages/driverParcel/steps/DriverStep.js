import { Network, toast, debounce } from '../../../core/utils.js';
import { Modal } from 'bootstrap';

export default {
    init(wizard) {
        this.wizard = wizard;
        this.bindSearch();
        document.getElementById('addDriver')
            ?.addEventListener('click', () => this.openModal());
    },

    bindSearch() {
        const input = document.getElementById('search-driver');
        const body = document.getElementById('driverBody');

        input.addEventListener('input',
            debounce(() => this.search(input.value, body), 300)
        );

        body.addEventListener('click', e => {
            if (e.target.closest('button')) return;

            const row = e.target.closest('tr');
            if (!row || !row.dataset.id) return;

            this.select(row.dataset.id);
        });
    },

    async search(q, body) {
        if (q.length < 2) return;
        try {
            const res = await Network.post('/drivers/search', { search: q });
            body.innerHTML = res.html || '';
        } catch {
            toast('حدث خطأ أثناء البحث', 'error');
        }
    },

    async select(id) {
        const row = document.querySelector(`#driverBody tr[data-id="${id}"]`);
        
        document.querySelectorAll('#driverBody tr').forEach((r) => {
            r.classList.remove('selected');
        });
        
        if (row) {
            row.classList.add('selected');
        }

        try {
            const res = await Network.get(`/drivers/${id}`);
            if (!res.driver) throw new Error();
    
            this.wizard.selectedDriver = res.driver;
    
            document.getElementById('driverName').value = res.driver.driverName;
            document.getElementById('driverNumber').value = res.driver.driverPhone;
            document.getElementById('driverId').value = res.driver.driverId;
    
            toast('تم اختيار السائق', 'success');
            this.wizard.nextStep();
        } catch {
            if (row) {
                row.classList.remove('selected');
            }
            toast('فشل تحميل بيانات السائق', 'error');
        }
    },

    openModal() {
        const modalElement = document.getElementById('appModal');
        const modalBody = document.getElementById('appModalBody');
        const template = document.getElementById('driverModalTemplate');
        
        if (!modalElement || !modalBody || !template) {
            toast('خطأ في فتح النافذة', 'error');
            return;
        }

        // Clone template content
        const templateContent = template.content.cloneNode(true);
        modalBody.innerHTML = '';
        modalBody.appendChild(templateContent);
        
        // Initialize Bootstrap modal
        const modalInstance = new Modal(modalElement, {
            backdrop: true,
            keyboard: true
        });
        
        // Show modal
        requestAnimationFrame(() => {
            modalInstance.show();
            document.body.classList.add('modal-open');
        });

        // Bind modal events
        this.bindDriverModal(modalInstance, modalElement);
    },

    bindDriverModal(modalInstance, modalElement) {
        const closeBtn = document.getElementById('closeDriverModal');
        const cancelBtn = document.getElementById('cancelDriverBtn');
        const submitBtn = document.getElementById('submitDriverBtn');
        const form = document.getElementById('addDriverForm');

        const closeModal = () => {
            modalInstance.hide();
            document.body.classList.remove('modal-open');
        };

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        // Cancel button
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }

        // Handle modal hidden event
        modalElement.addEventListener('hidden.bs.modal', () => {
            document.body.classList.remove('modal-open');
            const modalBody = document.getElementById('appModalBody');
            if (modalBody) {
                modalBody.innerHTML = '';
            }
        }, { once: true });

        // Submit button
        if (form && submitBtn) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.submitDriverForm(form, modalInstance);
            });
        }
    },

    async submitDriverForm(form, modalInstance) {
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Disable submit button
        const submitBtn = document.getElementById('submitDriverBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
        }

        try {
            const formData = new FormData(form);
            const data = {
                driverName: formData.get('driverName'),
                driverPhone: formData.get('driverPhone'),
            };

            const res = await Network.post('/drivers', data);

            if (res.success || res.driver) {
                // Close modal
                modalInstance.hide();
                document.body.classList.remove('modal-open');

                toast(res.message || 'تم إضافة السائق بنجاح', 'success');

                // Refresh driver list by searching with the new driver's phone
                const searchInput = document.getElementById('search-driver');
                const tbody = document.getElementById('driverBody');
                
                if (searchInput && tbody && data.driverPhone) {
                    searchInput.value = data.driverPhone;
                    await this.search(data.driverPhone, tbody);
                }
            } else {
                toast(res.message || 'فشل إضافة السائق', 'error');
            }
        } catch (err) {
            // Validation errors are already shown by Network.post
            // Only show generic error if it's not a validation error
            if (!err.message || !err.message.includes('فشل التحقق')) {
                if (App.config?.debug) {
                    console.error('[DriverStep] Submit error:', err);
                }
            }
        } finally {
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> حفظ السائق';
            }
        }
    }
    
};
