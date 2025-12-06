<template id="template-step-parcel">

<div class="parcel-form-container">
    <h1 class="form-title">
        <span class="form-icon">📦</span>
        ارسالية شحن
    </h1>

    <form id="parcelForm" class="parcel-form">

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
                    <input type="text" id="parcelid" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label for="nameS">
                        <span class="label-icon">👤</span>
                        اسم المرسل
                    </label>
                    <input type="text" id="nameS" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label for="phoneS">
                        <span class="label-icon">📱</span>
                        هاتف المرسل
                    </label>
                    <input type="text" id="phoneS" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label for="date">
                        <span class="label-icon">📅</span>
                        التاريخ
                    </label>
                    <input type="text" id="date" readonly class="readonly-input">
                </div>
            </div>
        </div>

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
                    <label>
                        <span class="label-icon">👤</span>
                        اسم المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="recvName" required>
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">📱</span>
                        هاتف المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="recvPhone" required>
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">🏢</span>
                        المكتب المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <select id="recvOffice" required></select>
                </div>

                <div class="form-group form-group-full">
                    <label>
                        <span class="label-icon">🏠</span>
                        المكان المرسل اليه
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="recvAddress" required>
                </div>
            </div>
        </div>

        <div class="form-section payment-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">💳</span>
                    معلومات الدفع
                </h3>
            </div>

            <div class="payment-fields-grid">

                <div class="form-group payment-amount-group">
                    <label>
                        <span class="label-icon">💰</span>
                        الرسوم
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="cost" min="0" step="0.01" value="0" required>
                </div>

                <div class="form-group payment-select-group">
                    <label><span class="label-icon">💱</span>العملة</label>
                    <select id="currency">
                        <option value="JD">دينار</option>
                        <option value="USD">دولار</option>
                        <option value="IQD">دينار عراقي</option>
                        <option value="SYP">ليرة سوري</option>
                        <option value="SAR">ريال سعودي</option>
                    </select>
                </div>

                <div class="form-group payment-select-group">
                    <label><span class="label-icon">✅</span>حالة الدفع</label>
                    <select id="paymentStatus">
                        <option value="paid">مدفوع</option>
                        <option value="unpaid">غير مدفوع</option>
                        <option value="LaterPaid">تم الدفع لاحقا</option>
                    </select>
                </div>

                <div class="form-group payment-select-group">
                    <label><span class="label-icon">💳</span>طريقة الدفع</label>
                    <select id="paymentMethod">
                        <option value="cash">نقدي</option>
                        <option value="bank">حوالة بنكية</option>
                    </select>
                </div>

            </div>

            <div class="form-row hidden" id="paymentRestRow">
                <div class="form-group form-group-half">
                    <label>
                        <span class="label-icon">💵</span>
                        واصل
                    </label>
                    <input type="number" id="costRest" step="0.01" min="0" value="0">
                </div>

                <div class="form-group form-group-half" id="mainOfficePaidContainer">
                    <label class="toggle-container">
                        <span class="toggle-label">
                            <span class="label-icon">🏢</span>
                            الدفع في المكتب
                        </span>
                        <input type="checkbox" id="paidInMainOffice" class="toggle-checkbox">
                        <div class="toggle-switch"></div>
                    </label>
                </div>

            </div>
        </div>

        <div class="form-section packages-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">📦</span>
                    أصناف الارسالية
                </h3>
            </div>

            <div class="package-quantity-selector">
                <div class="form-group">
                    <label>
                        <span class="label-icon">🔢</span>
                        عدد الاصناف
                    </label>
                    <select id="packageCount">
                        @for($i=1;$i<=10;$i++)
                            <option value="{{ $i }}" {{ $i === 1 ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <button type="button" class="btn-add-package" id="addPackageBtn">
                    <span class="btn-icon">➕</span>
                    إضافة صنف
                </button>
            </div>

            <div class="packages-details" id="packagesContainer">
                <div class="package-detail" data-package-index="1">
                    <div class="package-header">
                        <h4 class="package-number">الصنف 1</h4>
                    </div>
                    <div class="package-content">
                        <div class="form-group">
                            <label>
                                <span class="label-icon">🔢</span>
                                العدد
                            </label>
                            <input type="number" class="pkg-qun" value="1" min="1">
                        </div>
                        <div class="form-group form-group-full">
                            <label>
                                <span class="label-icon">📝</span>
                                الوصف
                            </label>
                            <textarea class="pkg-desc" rows="4"></textarea>
                        </div>
                    </div>

                    <button type="button" class="btn-delete-package" data-delete-package>
                        <span class="btn-icon">🗑️</span>
                        حذف
                    </button>
                </div>
            </div>

        </div>

    </form>
</div>

</template>
