@php
    $mode = $mode ?? 'add'; // 'add' or 'edit'
    $address = $address ?? [];
    $isEdit = $mode === 'edit';
@endphp

<div class="modal-header">
    <h5 class="modal-title">{{ $isEdit ? 'تعديل عنوان' : 'اضافة عنوان' }}</h5>
    <button type="button" class="btn-close" id="closeAddressModal" aria-label="Close"></button>
</div>
<form id="{{ $isEdit ? 'editAddressForm' : 'addAddressForm' }}">
    <div class="form-group">
        <label for="{{ $isEdit ? 'editAddressCity' : 'addressCity' }}">المدينة <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $isEdit ? 'editAddressCity' : 'addressCity' }}" name="city" value="{{ $address['city'] ?? '' }}" required>
    </div>
    <div class="form-group">
        <label for="{{ $isEdit ? 'editAddressArea' : 'addressArea' }}">المنطقة <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $isEdit ? 'editAddressArea' : 'addressArea' }}" name="area" value="{{ $address['area'] ?? '' }}" required>
    </div>
    <div class="form-group">
        <label for="{{ $isEdit ? 'editAddressStreet' : 'addressStreet' }}">اسم الشارع <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $isEdit ? 'editAddressStreet' : 'addressStreet' }}" name="street" value="{{ $address['street'] ?? '' }}" required>
    </div>
    <div class="form-group">
        <label for="{{ $isEdit ? 'editAddressBuilding' : 'addressBuilding' }}">رقم المبنى <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $isEdit ? 'editAddressBuilding' : 'addressBuilding' }}" name="buildingNumber" value="{{ $address['buildingNumber'] ?? '' }}" required>
    </div>
    <div class="form-group">
        <label for="{{ $isEdit ? 'editAddressInfo' : 'addressInfo' }}">معلومات اضافية</label>
        <textarea class="form-control" id="{{ $isEdit ? 'editAddressInfo' : 'addressInfo' }}" name="info" rows="3">{{ $address['info'] ?? '' }}</textarea>
    </div>
</form>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" id="cancelAddressBtn">اغلاق</button>
    <button type="button" class="btn btn-primary" id="submitAddressBtn">{{ $isEdit ? 'حفظ التعديلات' : 'اضافة' }}</button>
</div>


