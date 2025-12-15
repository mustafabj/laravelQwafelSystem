import { Network, toast, parseIntSafe, debounce } from '../../../core/utils.js';
import Template from '../../../core/Template.js';

export default {
    selectedParcels: new Map(),

    init() {
        this.availableList = document.getElementById('availableParcelsList');
        this.selectedList = document.getElementById('selectedParcelsList');
        this.searchInput = document.getElementById('parcelDetailsSearch');

        if (!this.availableList || !this.selectedList || !this.searchInput) return;

        this.searchInput.addEventListener(
            'input',
            debounce(e => this.search(e.target.value), 400)
        );

        document.addEventListener('click', e => this.handleClick(e));
        document.addEventListener('change', e => this.handleChange(e));

        this.initDnD();
    },
    addParcelData(data, qty = 1) {
        if (!data?.detailId) return false;

        const current = this.selectedParcels.get(data.detailId)?.quantityTaken || 0;
        const maxAllowed = data.availableQuantity ?? data.totalQuantity ?? 0;

        if (current + qty > maxAllowed) {
            toast('الكمية تتجاوز المتاح', 'error');
            return false;
        }

        this.selectedParcels.set(data.detailId, {
            parcelDetailId: data.detailId,
            parcelId: data.parcelId || data.parcelNumber,
            quantityTaken: current + qty,
            parcelNumber: data.parcelNumber,
            customerName: data.customerName,
            description: data.detailInfo,
            totalQuantity: data.totalQuantity,
            availableQuantity: data.availableQuantity,
        });

        this.renderSelected();
        this.updateAvailableQuantities();
        toast('تمت إضافة الإرسالية', 'success');
        return true;
    },

    removeParcel(detailId) {
        if (!this.selectedParcels.has(detailId)) return;
        this.selectedParcels.delete(detailId);
        this.renderSelected();
        this.updateAvailableQuantities();
    },

    async search(term) {
        if (term.length < 2) {
            this.availableList.innerHTML = '';
            return;
        }

        try {
            const res = await Network.post(
                '/driver-parcels/search-parcel-details',
                { search: term, limit: 20 }
            );

            this.renderAvailable(res.parcelDetails || []);
        } catch {
            toast('حدث خطأ أثناء البحث', 'error');
        }
    },

    renderAvailable(list) {
        this.availableList.innerHTML = '';

        if (!list.length) {
            this.initAvailableSortable();
            return;
        }

        const groups = new Map();

        list.forEach(detail => {
            const key = detail.parcelNumber || `unknown_${detail.detailId}`;
            if (!groups.has(key)) {
                groups.set(key, {
                    parcelNumber: detail.parcelNumber,
                    customerName: detail.customerName,
                    items: []
                });
            }
            groups.get(key).items.push(detail);
        });

        groups.forEach(group => {
            const groupEl = document.createElement('div');
            groupEl.className = 'parcel-group-container';

            groupEl.innerHTML = `
                <div class="parcel-group-header">
                    <div class="parcel-group-title">
                        <i class="fas fa-box"></i>
                        <strong>إرسالية #${group.parcelNumber}</strong>
                        <span class="customer-name">${group.customerName || ''}</span>
                    </div>
                </div>
            `;

            const itemsEl = document.createElement('div');
            itemsEl.className = 'parcel-group-items';

            group.items.forEach(detail => {
                const el = Template.clone('tpl-available-parcel');
                if (!el) return;

                el.dataset.parcelData = JSON.stringify(detail);
                el.dataset.detailId = detail.detailId;
                el.dataset.totalQuantity = detail.totalQuantity;

                // Remove parcel number and customer name (already in group header)
                const parcelNumberEl = el.querySelector('[data-bind="parcelNumber"]');
                const customerNameEl = el.querySelector('[data-bind="customerName"]');
                if (parcelNumberEl) parcelNumberEl.remove();
                if (customerNameEl) customerNameEl.remove();

                // Fill template data
                Template.fill(el, {
                    description: detail.detailInfo || '',
                    availableText: `متاح: ${detail.availableQuantity}/${detail.totalQuantity}`
                });

                const qty = el.querySelector('.quantity-input');
                if (qty) {
                    qty.value = 1;
                    qty.max = detail.availableQuantity;
                    qty.dataset.detailId = detail.detailId;
                    qty.dataset.availableQuantity = detail.availableQuantity;
                }

                // Set data-detail-id on +/- buttons
                const decreaseBtn = el.querySelector('.btn-decrease-available-quantity');
                const increaseBtn = el.querySelector('.btn-increase-available-quantity');
                if (decreaseBtn) decreaseBtn.dataset.detailId = detail.detailId;
                if (increaseBtn) increaseBtn.dataset.detailId = detail.detailId;

                // Remove header if empty
                const headerEl = el.querySelector('.parcel-item-header');
                if (headerEl && headerEl.children.length === 0) {
                    headerEl.remove();
                }

                itemsEl.appendChild(el);
            });

            groupEl.appendChild(itemsEl);
            this.availableList.appendChild(groupEl);
        });

        this.initAvailableSortable();
        this.updateAvailableQuantities();
    },

    renderSelected() {
        this.selectedList.innerHTML = '';

        if (!this.selectedParcels.size) {
            const empty = document.getElementById('emptySelectedState');
            if (empty) this.selectedList.appendChild(empty.cloneNode(true));
            this.initSelectedSortable();
            return;
        }

        const groups = new Map();

        this.selectedParcels.forEach(parcel => {
            const key = parcel.parcelNumber || parcel.parcelId;
            if (!groups.has(key)) {
                groups.set(key, {
                    parcelNumber: parcel.parcelNumber,
                    customerName: parcel.customerName,
                    items: []
                });
            }
            groups.get(key).items.push(parcel);
        });

        groups.forEach(group => {
            const groupEl = document.createElement('div');
            groupEl.className = 'parcel-group-container';

            groupEl.innerHTML = `
                <div class="parcel-group-header">
                    <div class="parcel-group-title">
                        <i class="fas fa-box"></i>
                        <strong>إرسالية #${group.parcelNumber}</strong>
                        <span class="customer-name">${group.customerName || ''}</span>
                    </div>
                </div>
            `;

            const itemsEl = document.createElement('div');
            itemsEl.className = 'parcel-group-items';

            group.items.forEach(parcel => {
                const el = Template.clone('tpl-selected-parcel');
                if (!el) return;

                el.dataset.detailId = parcel.parcelDetailId;

                // Remove parcel number and customer name (already in group header)
                const parcelNumberEl = el.querySelector('[data-bind="parcelNumber"]');
                const customerNameEl = el.querySelector('[data-bind="customerName"]');
                if (parcelNumberEl) parcelNumberEl.remove();
                if (customerNameEl) customerNameEl.remove();

                // Fill template data
                Template.fill(el, {
                    description: parcel.description || ''
                });

                const input = el.querySelector('.quantity-edit-input');
                if (input) {
                    input.value = parcel.quantityTaken;
                    input.max = parcel.totalQuantity;
                    input.dataset.detailId = parcel.parcelDetailId;
                }

                const removeBtn = el.querySelector('.btn-remove-parcel');
                if (removeBtn) removeBtn.dataset.detailId = parcel.parcelDetailId;

                const decreaseBtn = el.querySelector('.btn-decrease-quantity');
                const increaseBtn = el.querySelector('.btn-increase-quantity');
                if (decreaseBtn) decreaseBtn.dataset.detailId = parcel.parcelDetailId;
                if (increaseBtn) increaseBtn.dataset.detailId = parcel.parcelDetailId;

                // Remove header if empty
                const headerEl = el.querySelector('.parcel-item-header');
                if (headerEl && headerEl.children.length === 0) {
                    headerEl.remove();
                }

                itemsEl.appendChild(el);
            });

            groupEl.appendChild(itemsEl);
            this.selectedList.appendChild(groupEl);
        });

        this.initSelectedSortable();
    },

    initDnD() {
        this.initAvailableSortable();
        this.initSelectedSortable();
    },

    initAvailableSortable() {
        if (!window.Sortable) return;
    
        this.availableList
            .querySelectorAll('.parcel-group-items')
            .forEach(container => {
                new Sortable(container, {
                    group: {
                        name: 'parcels',
                        pull: 'clone',
                        put: false,
                    },
                    draggable: '.parcel-item',
                    sort: false,
                    animation: 150,
                });
            });
    },

    initSelectedSortable() {
        if (!window.Sortable) return;

        new Sortable(this.selectedList, {
            group: { name: 'parcels', pull: true, put: true },
            animation: 150,
            onAdd: evt => {
                const raw = evt.item?.dataset?.parcelData;
                if (!raw) return evt.item.remove();

                const data = JSON.parse(raw);
                const qty = parseIntSafe(
                    evt.item.querySelector('.quantity-input')?.value,
                    1
                );

                evt.item.remove();
                this.addParcelData(data, qty);
            }
        });
    },

    handleClick(e) {
        const addBtn = e.target.closest('.btn-add-parcel');
        if (addBtn) {
            const item = addBtn.closest('.parcel-item');
            const data = JSON.parse(item.dataset.parcelData);
            const qty = parseIntSafe(item.querySelector('.quantity-input').value, 1);
            this.addParcelData(data, qty);
            return;
        }

        const removeBtn = e.target.closest('.btn-remove-parcel');
        if (removeBtn) {
            this.removeParcel(parseIntSafe(removeBtn.dataset.detailId));
            return;
        }

        // Handle decrease quantity for available items
        const decreaseAvailableBtn = e.target.closest('.btn-decrease-available-quantity');
        if (decreaseAvailableBtn) {
            const detailId = parseIntSafe(decreaseAvailableBtn.dataset.detailId);
            const item = decreaseAvailableBtn.closest('.parcel-item');
            const qtyInput = item?.querySelector('.quantity-input');
            if (qtyInput && detailId) {
                const current = parseIntSafe(qtyInput.value, 1);
                qtyInput.value = Math.max(1, current - 1);
            }
            return;
        }

        // Handle increase quantity for available items
        const increaseAvailableBtn = e.target.closest('.btn-increase-available-quantity');
        if (increaseAvailableBtn) {
            const detailId = parseIntSafe(increaseAvailableBtn.dataset.detailId);
            const item = increaseAvailableBtn.closest('.parcel-item');
            const qtyInput = item?.querySelector('.quantity-input');
            if (qtyInput && detailId) {
                const current = parseIntSafe(qtyInput.value, 1);
                const max = parseIntSafe(qtyInput.max, 999);
                qtyInput.value = Math.min(max, current + 1);
            }
            return;
        }

        // Handle decrease quantity for selected items
        const decreaseBtn = e.target.closest('.btn-decrease-quantity');
        if (decreaseBtn) {
            const detailId = parseIntSafe(decreaseBtn.dataset.detailId);
            if (detailId) {
                const parcel = this.selectedParcels.get(detailId);
                if (parcel) {
                    const newQty = Math.max(1, parcel.quantityTaken - 1);
                    parcel.quantityTaken = newQty;
                    this.selectedParcels.set(detailId, parcel);
                    this.renderSelected();
                    this.updateAvailableQuantities();
                }
            }
            return;
        }

        // Handle increase quantity for selected items
        const increaseBtn = e.target.closest('.btn-increase-quantity');
        if (increaseBtn) {
            const detailId = parseIntSafe(increaseBtn.dataset.detailId);
            if (detailId) {
                const parcel = this.selectedParcels.get(detailId);
                if (parcel) {
                    const newQty = parcel.quantityTaken + 1;
                    if (newQty > parcel.totalQuantity) {
                        toast('الكمية تتجاوز المتاح', 'error');
                        return;
                    }
                    parcel.quantityTaken = newQty;
                    this.selectedParcels.set(detailId, parcel);
                    this.renderSelected();
                    this.updateAvailableQuantities();
                }
            }
            return;
        }
    },

    handleChange(e) {
        const input = e.target.closest('.quantity-edit-input');
        if (!input) return;

        const detailId = parseIntSafe(input.dataset.detailId);
        const newQty = parseIntSafe(input.value, 1);
        const parcel = this.selectedParcels.get(detailId);

        if (!parcel) return;

        if (newQty < 1) {
            this.removeParcel(detailId);
            return;
        }

        if (newQty > parcel.totalQuantity) {
            toast('الكمية تتجاوز المتاح', 'error');
            input.value = parcel.quantityTaken;
            return;
        }

        parcel.quantityTaken = newQty;
        this.selectedParcels.set(detailId, parcel);
        this.updateAvailableQuantities();
    },

    updateAvailableQuantities() {
        this.availableList.querySelectorAll('.parcel-item').forEach(item => {
            const detailId = parseIntSafe(item.dataset.detailId);
            const total = parseIntSafe(item.dataset.totalQuantity);
            const selected = this.selectedParcels.get(detailId)?.quantityTaken || 0;
            const available = Math.max(0, total - selected);

            item.querySelector('.available-badge').textContent =
                `متاح: ${available}/${total}`;

            const input = item.querySelector('.quantity-input');
            input.max = available;
            if (parseIntSafe(input.value) > available) {
                input.value = available || 1;
            }
        });
    },

    hasParcels() {
        return this.selectedParcels.size > 0;
    },

    getSelected() {
        return [...this.selectedParcels.values()];
    }
};
