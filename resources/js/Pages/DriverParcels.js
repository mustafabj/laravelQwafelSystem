class DriverParcels {
    constructor() {
        this.selectedParcels = new Map();
        this.form = null;
        this.initialized = false;
    }

    init() {
        if (this.initialized) {
            if (App.config.debug) {
                console.log('[DriverParcels] Already initialized');
            }
            return;
        }

        this.form = document.getElementById('driverParcelForm');
        if (!this.form) {
            return;
        }

        this.bindEvents();
        this.initTripSelection();
        this.initFinancialCalculations();
        this.initParcelSearch();
        this.initDragAndDrop();
        this.initialized = true;

        if (App.config.debug) {
            console.log('[DriverParcels] Initialized');
        }
    }

    bindEvents() {
        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Add parcel buttons (using event delegation to handle dynamically added buttons)
        document.addEventListener('click', (e) => {
            const addBtn = e.target.closest('.btn-add-parcel');
            if (addBtn) {
                this.addParcel(e);
            }
            
            const removeBtn = e.target.closest('.btn-remove-parcel');
            if (removeBtn) {
                const detailId = parseInt(removeBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.removeParcel(detailId);
                }
            }
            
            const decreaseBtn = e.target.closest('.btn-decrease-quantity');
            if (decreaseBtn) {
                const detailId = parseInt(decreaseBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.decreaseQuantity(detailId);
                }
            }
            
            const increaseBtn = e.target.closest('.btn-increase-quantity');
            if (increaseBtn) {
                const detailId = parseInt(increaseBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.increaseQuantity(detailId);
                }
            }
            
            const decreaseAvailableBtn = e.target.closest('.btn-decrease-available-quantity');
            if (decreaseAvailableBtn) {
                const detailId = parseInt(decreaseAvailableBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.decreaseAvailableQuantity(detailId);
                }
            }
            
            const increaseAvailableBtn = e.target.closest('.btn-increase-available-quantity');
            if (increaseAvailableBtn) {
                const detailId = parseInt(increaseAvailableBtn.dataset.detailId);
                if (!isNaN(detailId)) {
                    this.increaseAvailableQuantity(detailId);
                }
            }
        });
        
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
    }

    initTripSelection() {
        const tripSelect = document.getElementById('tripId');
        const driverNameInput = document.getElementById('driverName');
        const sendToInput = document.getElementById('sendTo');

        if (tripSelect && driverNameInput && sendToInput) {
            tripSelect.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                if (selectedOption.value) {
                    const driverName = selectedOption.dataset.driverName || '';
                    const destination = selectedOption.dataset.destination || '';
                    
                    driverNameInput.value = driverName;
                    sendToInput.value = destination;
                }
            });
        }
    }

    initFinancialCalculations() {
        const costInput = document.getElementById('cost');
        const paidInput = document.getElementById('paid');
        const costRestInput = document.getElementById('costRest');

        if (costInput && paidInput && costRestInput) {
            const calculateRest = () => {
                const cost = parseFloat(costInput.value) || 0;
                const paid = parseFloat(paidInput.value) || 0;
                const rest = Math.max(0, cost - paid);
                costRestInput.value = rest.toFixed(2);
            };

            costInput.addEventListener('input', calculateRest);
            paidInput.addEventListener('input', calculateRest);
        }
    }

    initParcelSearch() {
        const searchInput = document.getElementById('parcelDetailsSearch');
        const parcelsList = document.getElementById('availableParcelsList');
        const searchLoading = document.getElementById('searchLoading');
        const emptyState = document.getElementById('emptySearchState');

        if (!searchInput || !parcelsList) {
            return;
        }

        let searchTimeout;

        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.trim();

            clearTimeout(searchTimeout);

            if (searchTerm.length < 2) {
                parcelsList.innerHTML = '';
                if (emptyState) {
                    parcelsList.appendChild(emptyState.cloneNode(true));
                }
                return;
            }

            searchTimeout = setTimeout(() => {
                this.searchParcelDetails(searchTerm, parcelsList, searchLoading, emptyState);
            }, 500);
        });
    }

    async searchParcelDetails(searchTerm, parcelsList, searchLoading, emptyState) {
        if (searchLoading) {
            searchLoading.style.display = 'block';
        }

        try {
            const response = await fetch('/driver-parcels/search-parcel-details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ search: searchTerm, limit: 20 })
            });

            const data = await response.json();

            if (searchLoading) {
                searchLoading.style.display = 'none';
            }

            if (data.success) {
                parcelsList.innerHTML = '';
                
                if (data.parcelDetails && data.parcelDetails.length > 0) {
                    this.renderParcelDetails(data.parcelDetails, parcelsList);
                } else {
                    const noResults = document.createElement('div');
                    noResults.className = 'empty-state';
                    noResults.innerHTML = `
                        <i class="fas fa-search"></i>
                        <p>لا توجد نتائج للبحث</p>
                        <small>جرب البحث بكلمات مختلفة</small>
                    `;
                    parcelsList.appendChild(noResults);
                }
            } else {
                parcelsList.innerHTML = '';
                const errorDiv = document.createElement('div');
                errorDiv.className = 'empty-state';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>حدث خطأ أثناء البحث</p>
                    <small>${data.message || 'يرجى المحاولة مرة أخرى'}</small>
                `;
                parcelsList.appendChild(errorDiv);
            }
        } catch (error) {
            console.error('Error searching parcel details:', error);
            if (searchLoading) {
                searchLoading.style.display = 'none';
            }
            App.utils.showToast('حدث خطأ أثناء البحث', 'error');
        }
    }

    renderParcelDetails(parcelDetails, container) {
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

            // Add drag event listeners
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    detailId: detail.detailId,
                    parcelId: detail.parcelId
                }));
                item.classList.add('dragging');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
            });

            container.appendChild(item);
        });

        // No need to bind event listeners here - using event delegation in bindEvents()
    }

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

        // Get parcel details from data attribute
        const parcelData = JSON.parse(parcelItem.dataset.parcelData);
        const parcelId = parcelData.parcelId !== undefined && parcelData.parcelId !== null 
            ? (typeof parcelData.parcelId === 'string' ? parseInt(parcelData.parcelId) : parcelData.parcelId)
            : 0;
        const parcelNumber = parcelData.parcelNumber || '';
        const customerName = parcelData.customerName || '';
        const description = parcelData.detailInfo || '';

        // Debug: log parcel data
        if (App.config.debug) {
            console.log('Adding parcel:', { detailId, parcelId, parcelNumber, customerName, parcelData });
        }

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
    }

    getAvailableQuantity(detailId, totalQuantity) {
        const selected = this.selectedParcels.get(detailId);
        const selectedQuantity = selected ? selected.quantityTaken : 0;
        return Math.max(0, totalQuantity - selectedQuantity);
    }

    removeParcel(detailId) {
        if (this.selectedParcels.has(detailId)) {
            this.selectedParcels.delete(detailId);
            this.updateSelectedParcelsList();
            this.updateAvailableQuantities();
            App.utils.showToast('تم إزالة الإرسالية', 'success');
        }
    }

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
    }

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
    }

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
    }

    updateSelectedParcelsList() {
        const selectedSection = document.getElementById('selectedParcelsSection');
        const selectedList = document.getElementById('selectedParcelsList');
        const selectedCount = document.getElementById('selectedCount');
        const emptySelectedState = document.getElementById('emptySelectedState');

        if (!selectedSection || !selectedList) {
            return;
        }

        if (this.selectedParcels.size === 0) {
            selectedList.innerHTML = '';
            if (emptySelectedState) {
                selectedList.appendChild(emptySelectedState.cloneNode(true));
            }
            selectedList.classList.add('empty');
            if (selectedCount) {
                selectedCount.textContent = '0';
            }
            return;
        }

        selectedList.classList.remove('empty');
        selectedList.innerHTML = '';

        if (selectedCount) {
            selectedCount.textContent = this.selectedParcels.size;
        }

        // Group parcels by parcelId
        const groupedParcels = new Map();
        this.selectedParcels.forEach((parcel, detailId) => {
            // Ensure parcelId is a number for proper grouping
            // Use the original parcelId value, not parsed, to maintain consistency
            let parcelId = parcel.parcelId;
            
            // Convert to number if it's a string
            if (typeof parcelId === 'string') {
                parcelId = parseInt(parcelId);
            }
            
            // Ensure it's a valid number
            if (isNaN(parcelId) || parcelId === null || parcelId === undefined) {
                if (App.config.debug) {
                    console.warn('Invalid or missing parcelId for detail:', detailId, parcel);
                }
                // Use a unique key for items without parcelId (shouldn't happen, but just in case)
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
            groupedParcels.get(parcelId).items.push({
                detailId: detailId,
                parcel: parcel
            });
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
                <span class="parcel-group-count">${group.items.length} عنصر</span>
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

        // Reinitialize drag and drop for adding items from search
        this.initDragAndDrop();
    }

    initDragAndDrop() {
        const selectedList = document.getElementById('selectedParcelsList');
        const availableList = document.getElementById('availableParcelsList');

        if (!selectedList) {
            return;
        }

        // No need to make groups or items sortable - order doesn't matter

        // Enable drop on selected parcels list (only add listeners once)
        // Since updateSelectedParcelsList clears innerHTML but keeps the same element,
        // we check if listeners are already attached using a data attribute
        if (!selectedList.dataset.dropInitialized) {
            selectedList.dataset.dropInitialized = 'true';
            
            selectedList.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                selectedList.classList.add('drag-over');
            });

            selectedList.addEventListener('dragleave', () => {
                selectedList.classList.remove('drag-over');
            });

            selectedList.addEventListener('drop', (e) => {
                e.preventDefault();
                selectedList.classList.remove('drag-over');
                
                const availableList = document.getElementById('availableParcelsList');
                
                try {
                    const dragData = JSON.parse(e.dataTransfer.getData('text/plain'));
                    const detailId = parseInt(dragData.detailId);
                    const draggedItem = availableList.querySelector(`[data-detail-id="${detailId}"]`);
                    
                    if (draggedItem) {
                        const quantityInput = draggedItem.querySelector('.quantity-input');
                        const quantity = parseInt(quantityInput.value) || 1;
                        this.addParcelFromDrag(draggedItem, quantity);
                    }
                } catch (error) {
                    // Fallback for old format
                    const detailId = parseInt(e.dataTransfer.getData('text/plain'));
                    const draggedItem = availableList.querySelector(`[data-detail-id="${detailId}"]`);
                    
                    if (draggedItem) {
                        const quantityInput = draggedItem.querySelector('.quantity-input');
                        const quantity = parseInt(quantityInput.value) || 1;
                        this.addParcelFromDrag(draggedItem, quantity);
                    }
                }
            });
        }
    }

    addParcelFromDrag(item, quantity = 1) {
        const detailId = parseInt(item.dataset.detailId);
        const totalQuantity = parseInt(item.dataset.total) || 0;

        const parcelData = JSON.parse(item.dataset.parcelData);
        // Ensure parcelId is preserved as-is (number) from the data
        const parcelId = parcelData.parcelId !== undefined && parcelData.parcelId !== null 
            ? (typeof parcelData.parcelId === 'string' ? parseInt(parcelData.parcelId) : parcelData.parcelId)
            : 0;
        const parcelNumber = parcelData.parcelNumber || '';
        const customerName = parcelData.customerName || '';
        const description = parcelData.detailInfo || '';

        // Debug: log parcel data
        if (App.config.debug) {
            console.log('Adding parcel from drag:', { detailId, parcelId, parcelNumber, customerName, parcelData });
        }

        // Validate parcelId exists
        if (!parcelId && App.config.debug) {
            console.error('Missing parcelId in parcelData:', parcelData);
        }

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
    }


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
    }

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
    }

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
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (this.selectedParcels.size === 0) {
            App.utils.showToast('يجب إضافة إرسالية واحدة على الأقل', 'error');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

        try {
            // Build form data object
            const formDataObj = {};
            const formData = new FormData(this.form);
            
            // Convert FormData to object
            for (const [key, value] of formData.entries()) {
                if (key === 'cost' || key === 'paid' || key === 'costRest') {
                    formDataObj[key] = value ? parseFloat(value) : 0;
                } else {
                    formDataObj[key] = value;
                }
            }
            
            // Add selected parcels
            formDataObj.parcelDetails = Array.from(this.selectedParcels.values()).map(parcel => ({
                parcelDetailId: parseInt(parcel.parcelDetailId),
                quantityTaken: parseInt(parcel.quantityTaken)
            }));

            const response = await fetch(this.form.action || window.location.href, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formDataObj)
            });

            const data = await response.json();

            if (data.success) {
                App.utils.showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = `/driver-parcels/${data.driverParcelId}`;
                }, 1500);
            } else {
                App.utils.showToast(data.message || 'حدث خطأ أثناء الحفظ', 'error');
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
    App.pages.DriverParcels = new DriverParcels();
    
    // Auto-initialize if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            App.pages.DriverParcels.init();
        });
    } else {
        App.pages.DriverParcels.init();
    }
}

export default App.pages.DriverParcels;
