<template id="tpl-selected-parcel">
    <div class="selected-parcel-item">
        <div class="selected-parcel-info">
            <div class="parcel-item-header">
                <strong data-bind="parcelNumber"></strong>
                <span class="customer-name" data-bind="customerName"></span>
            </div>
            <p class="parcel-description" data-bind="description"></p>
        </div>
        <div class="selected-parcel-quantity">
            <div class="quantity-controls">
                <button type="button" class="btn-decrease-quantity" data-detail-id="" title="تقليل الكمية">
                    <i class="fas fa-minus"></i>
                </button>
        <input type="number"
            class="quantity-edit-input"
            min="1"
                       value="1"
            data-detail-id="">
                <button type="button" class="btn-increase-quantity" data-detail-id="" title="زيادة الكمية">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <button type="button" class="btn-remove-parcel" data-detail-id="" title="حذف">
                <i class="fas fa-times"></i>
        </button>
        </div>
    </div>
</template>