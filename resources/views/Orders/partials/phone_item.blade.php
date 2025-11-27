@php
    $phoneNumber = $phoneNumber ?? '';
    $phoneIndex = $phoneIndex ?? 1;
@endphp

<div class="phone-item" data-phone-index="{{ $phoneIndex }}">
    <div class="phone-input-group">
        <label for="phone{{ $phoneIndex }}">رقم الهاتف {{ $phoneIndex }}</label>
        <div class="phone-input-wrapper">
            <input 
                type="text" 
                id="phone{{ $phoneIndex }}" 
                class="phone-input" 
                value="{{ $phoneNumber }}"
                placeholder="أدخل رقم الهاتف"
                data-phone-id="{{ $phoneIndex }}"
            />
            <div class="phone-actions">
                <button type="button" class="btn-select-phone" data-action="select">
                    تحديد الهاتف
                </button>
                <button type="button" class="btn-delete-phone" data-action="delete">
                    حذف الهاتف
                </button>
            </div>
        </div>
    </div>
</div>

