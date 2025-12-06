@php
    $index = $index ?? '__INDEX__'; 
@endphp

<div class="package-item" data-package-index="{{ $index }}">
    <div class="package-header">
        <h4 class="package-number">الصنف {{ $index }}</h4>
    </div>

    <div class="package-content">
        <div class="form-group">
            <label>
                <span class="label-icon">🔢</span>
                العدد
            </label>
            <input 
                type="number"
                class="qun-input"
                name="qun[]"
                min="1"
                value="1"
            >
        </div>

        <div class="form-group form-group-full">
            <label>
                <span class="label-icon">📝</span>
                الوصف
            </label>
            <textarea 
                class="desc-input"
                name="desc[]"
                rows="4"
            ></textarea>
        </div>
    </div>

    <button type="button" class="btn-delete-package" data-action="delete-package">
        <span class="btn-icon">🗑️</span>
        حذف
    </button>
</div>
