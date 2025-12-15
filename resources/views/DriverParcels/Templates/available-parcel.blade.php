
<template id="tpl-available-parcel">
    <div class="parcel-item">
        <div class="parcel-item-header">
            <strong data-bind="parcelNumber"></strong>
            <span data-bind="customerName"></span>
            <span class="available-badge" data-bind="availableText"></span>
        </div>

        <div class="parcel-item-body">
            <p data-bind="description"></p>

            <div class="parcel-actions">
                <div class="quantity-controls">
                    <button type="button" class="btn-decrease-available-quantity" data-detail-id="" title="تقليل الكمية">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="quantity-input" min="1" value="1">
                    <button type="button" class="btn-increase-available-quantity" data-detail-id="" title="زيادة الكمية">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-add-parcel">
                    إضافة
                </button>
            </div>
        </div>
    </div>
</template>
