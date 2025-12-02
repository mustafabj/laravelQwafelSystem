<div class="parcel-form-container">
    <h1 class="form-title">
        <span class="form-icon">📦</span>
        ارسالية شحن
    </h1>

    <form id="saveParcel" class="parcel-form">
        {{-- Sender Information (Readonly) --}}
        <div class="form-section sender-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">👤</span>
                    معلومات المرسل
                </h3>
                <span class="section-badge readonly-badge">معلومات جاهزة</span>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="parcelid">
                        <span class="label-icon">🔢</span>
                        رقم الارسالية
                    </label>
                    <input type="text" name="parcelid" id="parcelid" value="{{ $nextParcelNumber }}" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="nameS">
                        <span class="label-icon">👤</span>
                        اسم المرسل
                    </label>
                    <input type="text" name="nameS" id="nameS" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="phoneS">
                        <span class="label-icon">📱</span>
                        هاتف المرسل
                    </label>
                    <input type="text" name="phoneS" id="phoneS" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="date">
                        <span class="label-icon">📅</span>
                        التاريخ
                    </label>
                    <input type="text" name="date" id="date" readonly class="readonly-input">
                </div>
            </div>
        </div>

        {{-- Recipient Information --}}
        <div class="form-section recipient-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">📍</span>
                    معلومات المرسل اليه
                </h3>
                <span class="section-badge required-badge">مطلوب</span>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nameST">
                        <span class="label-icon">👤</span>
                        اسم المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nameST" id="nameST" required placeholder="أدخل اسم المرسل اليه">
                </div>
                <div class="form-group">
                    <label for="phoneST">
                        <span class="label-icon">📱</span>
                        هاتف المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="phoneST" id="phoneST" required placeholder="أدخل رقم الهاتف">
                </div>
                <div class="form-group">
                    <label for="officeST">
                        <span class="label-icon">🏢</span>
                        المكتب المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <select name="officeST" id="officeST" class="form-select" required>
                        <option value="">اختر المكتب</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->officeId }}">{{ $office->officeName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group form-group-full">
                    <label for="addressST">
                        <span class="label-icon">🏠</span>
                        المكان المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="addressST" id="addressST" required placeholder="أدخل العنوان الكامل">
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="form-section payment-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">💳</span>
                    معلومات الدفع
                </h3>
            </div>
            <div class="payment-fields-grid">
                <div class="form-group payment-amount-group">
                    <label for="cost">
                        <span class="label-icon">💰</span>
                        الرسوم
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="cost" id="cost" min="0" value="0" step="0.01" required placeholder="0.00" class="payment-input">
                </div>
                <div class="form-group payment-select-group">
                    <label for="currency">
                        <span class="label-icon">💱</span>
                        العملة
                    </label>
                    <select name="currency" id="currency" class="currency-select">
                        <option value="JD">دينار</option>
                        <option value="USD">دولار</option>
                        <option value="IQD">دينار عراقي</option>
                        <option value="SYP">ليرة سوري</option>
                        <option value="SAR">ريال سعودي</option>
                    </select>
                </div>
                <div class="form-group payment-select-group">
                    <label for="paymentPaid">
                        <span class="label-icon">✅</span>
                        حالة الدفع
                    </label>
                    <select name="paid" id="paymentPaid" class="currency-select">
                        <option value="paid">مدفوع</option>
                        <option value="unpaid">غير مدفوع</option>
                        <option value="LaterPaid">تم الدفع لاحقا</option>
                    </select>
                </div>
                <div class="form-group payment-select-group">
                    <label for="paymentMethod">
                        <span class="label-icon">💳</span>
                        طريقة الدفع
                    </label>
                    <select name="paidMethod" id="paymentMethod" class="currency-select">
                        <option value="cash">نقدي</option>
                        <option value="bank">حوالة بنكية</option>
                    </select>
                </div>
            </div>
            <div class="form-row hidden" id="paymentPks">
                <div class="form-group form-group-half">
                    <label for="costRest">
                        <span class="label-icon">💵</span>
                        واصل
                    </label>
                    <div class="currency-price-group">
                        <input type="number" name="costRest" id="costRest" min="0" value="0" step="0.01" placeholder="0.00">
                    </div>
                </div>
                @if($currentOffice)
                <div class="form-group form-group-half">
                    <label class="toggle-container">
                        <span class="toggle-label">
                            <span class="label-icon">🏢</span>
                            الدفع في {{ $currentOffice->officeName }}
                        </span>
                        <input type="checkbox" name="paidInMainOffice" id="paidInMainOffice" class="toggle-checkbox">
                        <div class="toggle-switch"></div>
                    </label>
                </div>
                @endif
            </div>
        </div>

        {{-- Package Items --}}
        <div class="form-section packages-section">
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
                    <select name="packagequnt" id="packagequnt" class="form-select package-quantity-select">
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ $i === 1 ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <button type="button" class="btn-add-package" id="addPackageBtn">
                    <span class="btn-icon">➕</span>
                    إضافة صنف
                </button>
            </div>
            <div class="packages-details" id="packagesDet">
                <div class="package-detail" data-package-index="1">
                    <div class="package-header">
                        <h4 class="package-number">الصنف 1</h4>
                    </div>
                    <div class="package-content">
                        <div class="form-group">
                            <label for="qun1">
                                <span class="label-icon">🔢</span>
                                العدد
                            </label>
                            <input type="number" name="qun[]" id="qun1" class="qun-input" min="1" value="1" placeholder="1">
                        </div>
                        <div class="form-group form-group-full">
                            <label for="desc1">
                                <span class="label-icon">📝</span>
                                الوصف
                            </label>
                            <textarea name="desc[]" id="desc1" class="desc-input" rows="4" placeholder="أدخل وصف الصنف..."></textarea>
                        </div>
                    </div>
                    <button type="button" class="btn-delete-package" onclick="deletePackage(this)">
                        <span class="btn-icon">🗑️</span>
                        حذف
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
