import { Network, toast, debounce } from '../../../core/utils.js';

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
    }
    
};
