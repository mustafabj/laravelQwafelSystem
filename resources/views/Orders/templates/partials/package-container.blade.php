<div class="packages-section">
    <div class="section-header">
        <h3 class="section-title">
            <span class="section-icon">📦</span>
            أصناف الارسالية
        </h3>
    </div>

    <div class="package-quantity-selector">
        <div class="form-group">
            <label for="packagequnt">
                <span class="label-icon">🔢</span>
                عدد الاصناف
            </label>
            <select id="packagequnt" class="form-select package-quantity-select">
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <button type="button" class="btn-add-package" id="addPackageBtn" data-action="add-package">
            <span class="btn-icon">➕</span>
            إضافة صنف
        </button>
    </div>

    <div class="packages-details" id="packagesDet"></div>
</div>
