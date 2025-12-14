/**
 * ParcelsStep Module
 * Handles parcel selection step in DriverParcelWizard
 * Reuses logic from DriverParcels class
 */
App.pages = App.pages || {};
App.pages.DriverParcelWizard = App.pages.DriverParcelWizard || {};

App.pages.DriverParcelWizard.ParcelsStep = {
    selectedParcels: new Map(),
    eventsBound: false,

    init(wizard) {
        this.wizard = wizard;
        this.initParcelSearch();
        this.initDragAndDrop();
        if (!this.eventsBound) {
            this.bindEvents();
            this.eventsBound = true;
        }
        this.updateSummary();
    },

    bindEvents() {
        // Prevent duplicate event listeners
        if (this.eventsBound) {
            return;
        }

        // Add parcel buttons (using event delegation to handle dynamically added buttons)
        // Use a single event listener with proper event handling
        const clickHandler = (e) => {
            // Check for add button first
            const addBtn = e.target.closest('.btn-add-parcel');
            if (addBtn) {
                e.preventDefault();
                e.stopPropagation();
                this.addParcel(e);
                return;
            }
            
            // Check for remove button
            const removeBtn = e.target.closest('.btn-remove-parcel');
            if (removeBtn) {
                e.preventDefault();
                e.stopPropagation();
                const detailId = parseInt(removeBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.removeParcel(detailId);
                }
                return;
            }
            
            // Check for decrease quantity button (selected parcels)
            const decreaseBtn = e.target.closest('.btn-decrease-quantity');
            if (decreaseBtn) {
                e.preventDefault();
                e.stopPropagation();
                const detailId = parseInt(decreaseBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.decreaseQuantity(detailId);
                }
                return;
            }
            
            // Check for increase quantity button (selected parcels)
            const increaseBtn = e.target.closest('.btn-increase-quantity');
            if (increaseBtn) {
                e.preventDefault();
                e.stopPropagation();
                const detailId = parseInt(increaseBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.increaseQuantity(detailId);
                }
                return;
            }
            
            // Check for decrease available quantity button
            const decreaseAvailableBtn = e.target.closest('.btn-decrease-available-quantity');
            if (decreaseAvailableBtn) {
                e.preventDefault();
                e.stopPropagation();
                const detailId = parseInt(decreaseAvailableBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.decreaseAvailableQuantity(detailId);
                }
                return;
            }
            
            // Check for increase available quantity button
            const increaseAvailableBtn = e.target.closest('.btn-increase-available-quantity');
            if (increaseAvailableBtn) {
                e.preventDefault();
                e.stopPropagation();
                const detailId = parseInt(increaseAvailableBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.increaseAvailableQuantity(detailId);
                }
                return;
            }
        };
        
        document.addEventListener('click', clickHandler, true); // Use capture phase
        
        // Handle quantity input changes (on blur or Enter key)
        document.addEventListener('change', (e) => {
            const quantityInput = e.target.closest('.quantity-edit-input');
            if (quantityInput) {
                const detailId = parseInt(quantityInput.dataset.detailId);
                const newQuantity = parseInt(quantityInput.value) || 1;
                const totalQuantity = parseInt(quantityInput.dataset.totalQuantity) || 0;
                
                if (!isNaN(detailId)) {
                    this.updateQuantity(detailId, newQuantity, totalQuantity);
                }
            }
        });
        
        // Also handle Enter key press in quantity input
        document.addEventListener('keydown', (e) => {
            const quantityInput = e.target.closest('.quantity-edit-input');
            if (quantityInput && e.key === 'Enter') {
                e.preventDefault();
                const detailId = parseInt(quantityInput.dataset.detailId);
                const newQuantity = parseInt(quantityInput.value) || 1;
                const totalQuantity = parseInt(quantityInput.dataset.totalQuantity) || 0;
                
                if (!isNaN(detailId)) {
                    this.updateQuantity(detailId, newQuantity, totalQuantity);
                }
            }
            
            // Handle Enter key for available parcels quantity input
            const availableQuantityInput = e.target.closest('.quantity-input');
            if (availableQuantityInput && e.key === 'Enter') {
                e.preventDefault();
                // Just blur to trigger change event
                availableQuantityInput.blur();
            }
        });
    },

    initParcelSearch() {
        const searchInput = document.getElementById('parcelDetailsSearch');
        const parcelsList = document.getElementById('availableParcelsList');
        const searchLoading = document.getElementById('searchLoading');
        const emptyState = document.getElementById('emptySearchState');

        if (!searchInput || !parcelsList) return;

        let searchTimeout = null;
        const debounceDelay = 500;

        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.trim();

            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            if (searchTerm.length < 2) {
                if (emptyState) {
                    parcelsList.innerHTML = '';
                    parcelsList.appendChild(emptyState.cloneNode(true));
                }
                if (searchLoading) searchLoading.style.display = 'none';
                return;
            }

            if (searchLoading) searchLoading.style.display = 'block';

            searchTimeout = setTimeout(() => {
                this.searchParcelDetails(searchTerm, parcelsList, searchLoading, emptyState);
            }, debounceDelay);
        });
    },

    async searchParcelDetails(searchTerm, parcelsList, searchLoading, emptyState) {
        try {
            const response = await fetch('/driver-parcels/search-parcel-details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ search: searchTerm, limit: 20 }),
            });

            const result = await response.json();

            if (searchLoading) searchLoading.style.display = 'none';

            if (result.success && result.parcelDetails && result.parcelDetails.length > 0) {
                this.renderParcelDetails(result.parcelDetails, parcelsList);
            } else {
                parcelsList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>لا توجد نتائج</p>
                        <small>جرب البحث بكلمات مختلفة</small>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error searching parcel details:', error);
            if (searchLoading) searchLoading.style.display = 'none';
            App.utils.showToast('حدث خطأ أثناء البحث', 'error');
        }
    },

    renderParcelDetails(parcelDetails, container) {
        container.innerHTML = '';
        parcelDetails.forEach(detail => {
            const item = document.createElement('div');
            item.className = 'parcel-item';
            item.draggable = true;
            item.setAttribute('data-parcel-id', detail.parcelId);
            item.setAttribute('data-detail-id', detail.detailId);
            item.setAttribute('data-available', detail.availableQuantity);
            item.setAttribute('data-total', detail.totalQuantity);
            item.setAttribute('data-parcel-data', JSON.stringify(detail));

            item.innerHTML = `
                <div class="parcel-item-header">
                    <div class="parcel-info">
                        <strong>إرسالية #${detail.parcelNumber}</strong>
                        <span class="customer-name">${detail.customerName}</span>
                        ${detail.customerPassport ? `<span class="customer-passport">جواز: ${detail.customerPassport}</span>` : ''}
                    </div>
                    <div class="parcel-quantity">
                        <span class="available-badge">متاح: ${detail.availableQuantity} / ${detail.totalQuantity}</span>
                    </div>
                </div>
                <div class="parcel-item-body">
                    <p class="parcel-description">${detail.detailInfo}</p>
                    <div class="parcel-actions">
                        <label class="quantity-input-label">
                            الكمية المطلوبة:
                        </label>
                        <div class="quantity-controls">
                            <button type="button" class="btn-decrease-available-quantity" data-detail-id="${detail.detailId}" title="تقليل الكمية">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="quantity-input" 
                                   min="1" 
                                   max="${detail.availableQuantity}" 
                                   value="1"
                                   data-detail-id="${detail.detailId}"
                                   data-available-quantity="${detail.availableQuantity}">
                            <button type="button" class="btn-increase-available-quantity" data-detail-id="${detail.detailId}" title="زيادة الكمية">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <button type="button" class="btn-add-parcel" data-detail-id="${detail.detailId}">
                            <i class="fas fa-plus"></i> إضافة
                        </button>
                    </div>
                </div>
            `;

            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    detailId: detail.detailId,
                    parcelId: detail.parcelId,
                    quantity: item.querySelector('.quantity-input').value
                }));
                item.classList.add('dragging');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
            });

            container.appendChild(item);
        });

        // No need to bind add buttons here - using event delegation in bindEvents()
        this.updateAvailableQuantities();
    },

    addParcel(e) {
        const btn = e.target.closest('.btn-add-parcel');
        const detailId = parseInt(btn.dataset.detailId);
        const parcelItem = btn.closest('.parcel-item');
        const quantityInput = parcelItem.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput.value) || 1;
        const totalQuantity = parseInt(parcelItem.dataset.total) || 0;

        if (quantity < 1) {
            App.utils.showToast('الكمية يجب أن تكون أكبر من صفر', 'error');
            return;
        }

        const parcelData = JSON.parse(parcelItem.dataset.parcelData);
        const parcelId = parcelData.parcelId !== undefined && parcelData.parcelId !== null 
            ? (typeof parcelData.parcelId === 'string' ? parseInt(parcelData.parcelId) : parcelData.parcelId)
            : 0;
        const parcelNumber = parcelData.parcelNumber || '';
        const customerName = parcelData.customerName || '';
        const description = parcelData.detailInfo || '';

        // Check if parcel detail already exists
        if (this.selectedParcels.has(detailId)) {
            // Update quantity instead of replacing
            const existing = this.selectedParcels.get(detailId);
            const newQuantity = existing.quantityTaken + quantity;
            
            // Check if new quantity exceeds total available
            if (newQuantity > totalQuantity) {
                App.utils.showToast(`الكمية الإجمالية (${newQuantity}) تتجاوز الكمية المتاحة (${totalQuantity})`, 'error');
                return;
            }
            
            // Update quantity
            existing.quantityTaken = newQuantity;
            this.selectedParcels.set(detailId, existing);
            this.updateSelectedParcelsList();
            this.updateAvailableQuantities();
            App.utils.showToast(`تم تحديث الكمية إلى ${newQuantity}`, 'success');
        } else {
            // Check if quantity exceeds available
            const available = this.getAvailableQuantity(detailId, totalQuantity);
            if (quantity > available) {
                App.utils.showToast(`الكمية المطلوبة (${quantity}) تتجاوز الكمية المتاحة (${available})`, 'error');
                return;
            }

            // Add new parcel detail
            this.selectedParcels.set(detailId, {
                parcelDetailId: detailId,
                parcelId: parcelId,
                quantityTaken: quantity,
                parcelNumber,
                customerName,
                description,
                available: available,
                totalQuantity
            });

            this.updateSelectedParcelsList();
            this.updateAvailableQuantities();
            App.utils.showToast('تم إضافة الإرسالية بنجاح', 'success');
        }
    },

    getAvailableQuantity(detailId, totalQuantity) {
        const selected = this.selectedParcels.get(detailId);
        const selectedQuantity = selected ? selected.quantityTaken : 0;
        return Math.max(0, totalQuantity - selectedQuantity);
    },

    removeParcel(detailId) {
        if (this.selectedParcels.has(detailId)) {
            this.selectedParcels.delete(detailId);
            this.updateSelectedParcelsList();
            this.updateAvailableQuantities();
            App.utils.showToast('تم إزالة الإرسالية', 'success');
        }
    },

    decreaseQuantity(detailId) {
        if (!this.selectedParcels.has(detailId)) {
            return;
        }

        const parcel = this.selectedParcels.get(detailId);
        const newQuantity = Math.max(1, parcel.quantityTaken - 1);

        if (newQuantity === parcel.quantityTaken) {
            return; // Already at minimum
        }

        parcel.quantityTaken = newQuantity;
        this.selectedParcels.set(detailId, parcel);
        this.updateSelectedParcelsList();
        this.updateAvailableQuantities();
        App.utils.showToast(`تم تقليل الكمية إلى ${newQuantity}`, 'success');
    },

    increaseQuantity(detailId) {
        if (!this.selectedParcels.has(detailId)) {
            return;
        }

        const parcel = this.selectedParcels.get(detailId);
        const newQuantity = parcel.quantityTaken + 1;

        // Check if new quantity exceeds total available
        if (newQuantity > parcel.totalQuantity) {
            App.utils.showToast(`الكمية الإجمالية (${newQuantity}) تتجاوز الكمية المتاحة (${parcel.totalQuantity})`, 'error');
            return;
        }

        parcel.quantityTaken = newQuantity;
        this.selectedParcels.set(detailId, parcel);
        this.updateSelectedParcelsList();
        this.updateAvailableQuantities();
        App.utils.showToast(`تم زيادة الكمية إلى ${newQuantity}`, 'success');
    },

    updateQuantity(detailId, newQuantity, totalQuantity) {
        if (!this.selectedParcels.has(detailId)) {
            return;
        }

        if (newQuantity < 1) {
            // Remove if quantity is 0 or less
            this.removeParcel(detailId);
            return;
        }

        if (newQuantity > totalQuantity) {
            App.utils.showToast(`الكمية المطلوبة (${newQuantity}) تتجاوز الكمية المتاحة (${totalQuantity})`, 'error');
            // Reset to current quantity
            const parcel = this.selectedParcels.get(detailId);
            const quantityInput = document.querySelector(`.quantity-edit-input[data-detail-id="${detailId}"]`);
            if (quantityInput) {
                quantityInput.value = parcel.quantityTaken;
            }
            return;
        }

        const parcel = this.selectedParcels.get(detailId);
        parcel.quantityTaken = newQuantity;
        this.selectedParcels.set(detailId, parcel);
        this.updateAvailableQuantities();
        App.utils.showToast(`تم تحديث الكمية إلى ${newQuantity}`, 'success');
    },

    decreaseAvailableQuantity(detailId) {
        const parcelItem = document.querySelector(`.parcel-item[data-detail-id="${detailId}"]`);
        if (!parcelItem) {
            return;
        }

        const quantityInput = parcelItem.querySelector('.quantity-input');
        if (!quantityInput) {
            return;
        }

        const currentQuantity = parseInt(quantityInput.value) || 1;
        const newQuantity = Math.max(1, currentQuantity - 1);

        if (newQuantity === currentQuantity) {
            return; // Already at minimum
        }

        quantityInput.value = newQuantity;
    },

    increaseAvailableQuantity(detailId) {
        const parcelItem = document.querySelector(`.parcel-item[data-detail-id="${detailId}"]`);
        if (!parcelItem) {
            return;
        }

        const quantityInput = parcelItem.querySelector('.quantity-input');
        if (!quantityInput) {
            return;
        }

        const currentQuantity = parseInt(quantityInput.value) || 1;
        const availableQuantity = parseInt(quantityInput.dataset.availableQuantity) || 0;
        const newQuantity = currentQuantity + 1;

        // Check if new quantity exceeds available
        if (newQuantity > availableQuantity) {
            App.utils.showToast(`الكمية المطلوبة (${newQuantity}) تتجاوز الكمية المتاحة (${availableQuantity})`, 'error');
            return;
        }

        quantityInput.value = newQuantity;
    },

    updateAvailableQuantities() {
        const parcelItems = document.querySelectorAll('.parcel-item');
        
        parcelItems.forEach(item => {
            const detailId = parseInt(item.dataset.detailId);
            const totalQuantity = parseInt(item.dataset.total) || 0;
            const available = this.getAvailableQuantity(detailId, totalQuantity);
            const availableBadge = item.querySelector('.available-badge');
            const quantityInput = item.querySelector('.quantity-input');
            
            if (availableBadge) {
                availableBadge.textContent = `متاح: ${available} / ${totalQuantity}`;
            }
            
            if (quantityInput) {
                quantityInput.max = Math.max(0, available);
                quantityInput.dataset.availableQuantity = available;
                if (parseInt(quantityInput.value) > available) {
                    quantityInput.value = Math.max(1, Math.min(available, parseInt(quantityInput.value) || 1));
                }
            }
            
            // Update the data-available attribute for drag operations
            item.setAttribute('data-available', available);
        });
    },

    updateSelectedParcelsList() {
        const selectedList = document.getElementById('selectedParcelsList');
        const emptySelectedState = document.getElementById('emptySelectedState');

        if (!selectedList) return;

        if (this.selectedParcels.size === 0) {
            selectedList.innerHTML = '';
            if (emptySelectedState) {
                selectedList.appendChild(emptySelectedState.cloneNode(true));
            }
            selectedList.classList.add('empty');
            return;
        }

        selectedList.classList.remove('empty');
        selectedList.innerHTML = '';

        // Group parcels by parcelId
        const groupedParcels = new Map();
        this.selectedParcels.forEach((parcel, detailId) => {
            let parcelId = parcel.parcelId;
            
            // Convert to number if it's a string
            if (typeof parcelId === 'string') {
                parcelId = parseInt(parcelId);
            }
            
            // Ensure it's a valid number
            if (isNaN(parcelId) || parcelId === null || parcelId === undefined) {
                parcelId = `unknown_${detailId}`;
            }
            
            if (!groupedParcels.has(parcelId)) {
                groupedParcels.set(parcelId, {
                    parcelId: parcelId,
                    parcelNumber: parcel.parcelNumber || 'غير محدد',
                    customerName: parcel.customerName || 'غير محدد',
                    items: []
                });
            }
            groupedParcels.get(parcelId).items.push({ detailId: detailId, parcel: parcel });
        });

        // Render grouped parcels
        groupedParcels.forEach((group, parcelId) => {
            const container = document.createElement('div');
            container.className = 'parcel-group-container';
            container.setAttribute('data-parcel-id', parcelId);

            const header = document.createElement('div');
            header.className = 'parcel-group-header';
            header.innerHTML = `
                <div class="parcel-group-title">
                    <i class="fas fa-box"></i>
                    <strong>إرسالية #${group.parcelNumber}</strong>
                    <span class="customer-name">${group.customerName}</span>
                </div>
            `;
            container.appendChild(header);

            const itemsContainer = document.createElement('div');
            itemsContainer.className = 'parcel-group-items';
            itemsContainer.setAttribute('data-parcel-id', parcelId);

            group.items.forEach(({ detailId, parcel }) => {
                const item = document.createElement('div');
                item.className = 'selected-parcel-item';
                item.setAttribute('data-detail-id', detailId);
                item.setAttribute('data-parcel-id', parcelId);
                item.innerHTML = `
                    <div class="selected-parcel-info">
                        <p class="parcel-description">${parcel.description}</p>
                    </div>
                    <div class="selected-parcel-quantity">
                        <div class="quantity-controls">
                            <button type="button" class="btn-decrease-quantity" data-detail-id="${detailId}" title="تقليل الكمية">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="quantity-edit-input" 
                                   value="${parcel.quantityTaken}" 
                                   min="1" 
                                   max="${parcel.totalQuantity}"
                                   data-detail-id="${detailId}"
                                   data-total-quantity="${parcel.totalQuantity}">
                            <button type="button" class="btn-increase-quantity" data-detail-id="${detailId}" title="زيادة الكمية">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <button type="button" class="btn-remove-parcel" data-detail-id="${detailId}" title="حذف الكل">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                itemsContainer.appendChild(item);
            });

            container.appendChild(itemsContainer);
            selectedList.appendChild(container);
        });

        // Re-initialize drop zone after updating the list
        this.initDragAndDrop();
    },

    initDragAndDrop() {
        const selectedList = document.getElementById('selectedParcelsList');
        if (!selectedList) return;

        // Remove old event listeners if they exist
        if (selectedList._dragOverHandler) {
            selectedList.removeEventListener('dragover', selectedList._dragOverHandler);
            selectedList.removeEventListener('dragleave', selectedList._dragLeaveHandler);
            selectedList.removeEventListener('drop', selectedList._dropHandler);
        }

        // Create new event handlers
        selectedList._dragOverHandler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'copy';
            if (!selectedList.classList.contains('drag-over')) {
                selectedList.classList.add('drag-over');
            }
        };

        selectedList._dragLeaveHandler = (e) => {
            // Only remove class if we're leaving the selectedList itself
            if (!selectedList.contains(e.relatedTarget)) {
                selectedList.classList.remove('drag-over');
            }
        };

        selectedList._dropHandler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            selectedList.classList.remove('drag-over');
            
            const availableList = document.getElementById('availableParcelsList');
            
            try {
                const dragData = JSON.parse(e.dataTransfer.getData('text/plain'));
                const detailId = parseInt(dragData.detailId);
                const draggedItem = availableList.querySelector(`[data-detail-id="${detailId}"]`);
                
                if (draggedItem) {
                    const quantityInput = draggedItem.querySelector('.quantity-input');
                    const quantity = parseInt(quantityInput ? quantityInput.value : dragData.quantity) || 1;
                    this.addParcelFromDrag(draggedItem, quantity);
                }
            } catch (error) {
                console.error('Error parsing drag data:', error);
                // Fallback for old format
                try {
                    const detailId = parseInt(e.dataTransfer.getData('text/plain'));
                    const draggedItem = availableList.querySelector(`[data-detail-id="${detailId}"]`);
                    
                    if (draggedItem) {
                        const quantityInput = draggedItem.querySelector('.quantity-input');
                        const quantity = parseInt(quantityInput ? quantityInput.value : 1) || 1;
                        this.addParcelFromDrag(draggedItem, quantity);
                    }
                } catch (fallbackError) {
                    console.error('Error in fallback drag handler:', fallbackError);
                }
            }
        };

        // Add event listeners
        selectedList.addEventListener('dragover', selectedList._dragOverHandler);
        selectedList.addEventListener('dragleave', selectedList._dragLeaveHandler);
        selectedList.addEventListener('drop', selectedList._dropHandler);
    },

    addParcelFromDrag(item, quantity = 1) {
        const detailId = parseInt(item.dataset.detailId);
        const totalQuantity = parseInt(item.dataset.total) || 0;

        const parcelData = JSON.parse(item.dataset.parcelData);
        const parcelId = parcelData.parcelId !== undefined && parcelData.parcelId !== null 
            ? (typeof parcelData.parcelId === 'string' ? parseInt(parcelData.parcelId) : parcelData.parcelId)
            : 0;
        const parcelNumber = parcelData.parcelNumber || '';
        const customerName = parcelData.customerName || '';
        const description = parcelData.detailInfo || '';

        // Check if parcel detail already exists
        if (this.selectedParcels.has(detailId)) {
            // Update quantity instead of replacing
            const existing = this.selectedParcels.get(detailId);
            const newQuantity = existing.quantityTaken + quantity;
            
            // Check if new quantity exceeds total available
            if (newQuantity > totalQuantity) {
                App.utils.showToast(`الكمية الإجمالية (${newQuantity}) تتجاوز الكمية المتاحة (${totalQuantity})`, 'error');
                return;
            }
            
            // Update quantity
            existing.quantityTaken = newQuantity;
            this.selectedParcels.set(detailId, existing);
            this.updateSelectedParcelsList();
            this.updateAvailableQuantities();
            App.utils.showToast(`تم تحديث الكمية إلى ${newQuantity}`, 'success');
        } else {
            // Check if quantity exceeds available
            const available = this.getAvailableQuantity(detailId, totalQuantity);
            if (quantity > available) {
                App.utils.showToast(`الكمية المطلوبة (${quantity}) تتجاوز الكمية المتاحة (${available})`, 'error');
                return;
            }

            // Add new parcel detail
            this.selectedParcels.set(detailId, {
                parcelDetailId: detailId,
                parcelId: parcelId,
                quantityTaken: quantity,
                parcelNumber,
                customerName,
                description,
                available: available,
                totalQuantity
            });

            this.updateSelectedParcelsList();
            this.updateAvailableQuantities();
            App.utils.showToast('تم إضافة الإرسالية بنجاح', 'success');
        }
    },

    getSelectedParcels() {
        return Array.from(this.selectedParcels.values());
    },

    updateSummary() {
        const summaryCard = document.getElementById('parcelSummaryCard');
        if (!summaryCard) return;

        // Get form values
        const driverName = document.getElementById('driverName')?.value || '-';
        const driverPhone = document.getElementById('driverNumber')?.value || '-';
        const tripSelect = document.getElementById('tripId');
        const tripName = tripSelect?.options[tripSelect.selectedIndex]?.text || '-';
        const destination = document.getElementById('sendTo')?.value || '-';
        const tripDate = document.getElementById('tripDate')?.value || '-';
        const parcelNumber = document.getElementById('parcelNumber')?.value || '-';
        const officeSelect = document.getElementById('officeId');
        const officeName = officeSelect?.options[officeSelect.selectedIndex]?.text || '-';
        const cost = document.getElementById('cost')?.value || '0';
        const paid = document.getElementById('paid')?.value || '0';
        const costRest = document.getElementById('costRest')?.value || '0';
        const currencySelect = document.getElementById('currency');
        const currency = currencySelect?.options[currencySelect.selectedIndex]?.text || '-';

        // Format date if available
        let formattedDate = '-';
        if (tripDate && tripDate !== '-') {
            try {
                const date = new Date(tripDate);
                formattedDate = date.toLocaleDateString('ar-EG', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            } catch (e) {
                formattedDate = tripDate;
            }
        }

        // Format currency values
        const formatCurrency = (value, currencyCode) => {
            const numValue = parseFloat(value) || 0;
            if (currencyCode === 'IQD') {
                return `${numValue.toFixed(2)} دينار`;
            } else if (currencyCode === 'USD') {
                return `$${numValue.toFixed(2)}`;
            } else if (currencyCode === 'EUR') {
                return `€${numValue.toFixed(2)}`;
            }
            return `${numValue.toFixed(2)} ${currencyCode}`;
        };

        const currencyCode = currencySelect?.value || 'IQD';

        // Update summary elements
        document.getElementById('summaryDriverName').textContent = driverName;
        document.getElementById('summaryDriverPhone').textContent = driverPhone;
        document.getElementById('summaryTripName').textContent = tripName;
        document.getElementById('summaryDestination').textContent = destination;
        document.getElementById('summaryTripDate').textContent = formattedDate;
        document.getElementById('summaryParcelNumber').textContent = parcelNumber;
        document.getElementById('summaryOffice').textContent = officeName;
        document.getElementById('summaryCost').textContent = formatCurrency(cost, currencyCode);
        document.getElementById('summaryPaid').textContent = formatCurrency(paid, currencyCode);
        document.getElementById('summaryCostRest').textContent = formatCurrency(costRest, currencyCode);
        document.getElementById('summaryCurrency').textContent = currency;

        // Show summary card if at least driver is selected
        if (driverName !== '-' && driverPhone !== '-') {
            summaryCard.style.display = 'block';
        } else {
            summaryCard.style.display = 'none';
        }
    }
};
