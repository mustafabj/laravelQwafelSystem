<div class="modal-header">
    <h5 class="modal-title">اضافة عميل</h5>
    <button type="button" class="btn-close" id="closeCustomerModal" aria-label="Close"></button>
</div>
<form id="addCustomerForm">
    <div class="customer-modal-content">
        {{-- Customer Information Section --}}
        <div class="customer-modal-section">
            <h3 class="section-title">
                <span class="section-icon">👤</span>
                معلومات العميل
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="FName">
                        <span class="label-icon">👤</span>
                        الاسم الاول
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="FName" id="FName" required placeholder="أدخل الاسم الاول">
                </div>
                <div class="form-group">
                    <label for="LName">
                        <span class="label-icon">👤</span>
                        الاسم الثاني
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="LName" id="LName" required placeholder="أدخل الاسم الثاني">
                </div>
                <div class="form-group">
                    <label for="passport">
                        <span class="label-icon">🛂</span>
                        رقم جواز السفر
                    </label>
                    <input type="text" name="passport" id="passport" placeholder="أدخل رقم جواز السفر">
                </div>
                <div class="form-group">
                    <label for="custState">
                        <span class="label-icon">📋</span>
                        حالة العميل
                    </label>
                    <input type="text" name="custState" id="custState" placeholder="أدخل حالة العميل">
                </div>
                <div class="form-group form-group-full">
                    <label for="phoneNumber">
                        <span class="label-icon">📱</span>
                        رقم الهاتف
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="phoneNumber" id="phoneNumber" required placeholder="أدخل رقم الهاتف">
                </div>
            </div>
        </div>

        {{-- Address Information Section --}}
        <div class="customer-modal-section">
            <h3 class="section-title">
                <span class="section-icon">📍</span>
                عنوان العميل
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="city">
                        <span class="label-icon">🏙️</span>
                        المدينة
                    </label>
                    <input type="text" name="city" id="city" placeholder="أدخل المدينة">
                </div>
                <div class="form-group">
                    <label for="aria">
                        <span class="label-icon">🗺️</span>
                        المنطقة
                    </label>
                    <input type="text" name="aria" id="aria" placeholder="أدخل المنطقة">
                </div>
                <div class="form-group">
                    <label for="streetName">
                        <span class="label-icon">🛣️</span>
                        اسم الشارع
                    </label>
                    <input type="text" name="streetName" id="streetName" placeholder="أدخل اسم الشارع">
                </div>
                <div class="form-group">
                    <label for="buildingNumber">
                        <span class="label-icon">🏢</span>
                        رقم المبنى
                    </label>
                    <input type="text" name="buildingNumber" id="buildingNumber" placeholder="أدخل رقم المبنى">
                </div>
                <div class="form-group form-group-full">
                    <label for="descAddress">
                        <span class="label-icon">📝</span>
                        معلومات اضافية
                    </label>
                    <textarea name="descAddress" id="descAddress" rows="4" placeholder="أدخل معلومات اضافية (اختياري)"></textarea>
                </div>
            </div>
        </div>
    </div>
</form>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" id="cancelCustomerBtn">اغلاق</button>
    <button type="button" class="btn btn-primary" id="submitCustomerBtn">اضافة</button>
</div>
