@extends('layouts.app')

@section('content')
<div class="order-wizard-container">
    <!-- Progress Bar -->
    <div class="wizard-progress noPrint">
        <div class="progress-bar">
            <div class="progress-fill" id="wizardProgressFill" style="width: 16.66%"></div>
        </div>
        <div class="progress-steps">
            <span class="progress-text">الخطوة <span id="currentStepNumber">1</span> من <span id="totalSteps">6</span></span>
        </div>
    </div>

    <!-- Start Tabs -->
    <div class="tabs noPrint">
        <ul>
            <li class="active" data-step="0">
                <span class="step-number">1</span>
                <img src="{{ asset('image/user2.png') }}" alt="user" />
                <span class="step-label">تحديد العميل</span>
            </li>
            <li data-step="1">
                <span class="step-number">2</span>
                <img src="{{ asset('image/phone-call.png') }}" alt="phone" />
                <span class="step-label">تحديد رقم الهاتف</span>
            </li>
            <li data-step="2">
                <span class="step-number">3</span>
                <img src="{{ asset('image/pin.png') }}" alt="pin" />
                <span class="step-label">تحديد العنوان</span>
            </li>
            <li data-step="3">
                <span class="step-number">4</span>
                <img src="{{ asset('image/box.png') }}" alt="box" />
                <span class="step-label">ارساليات او سفريات</span>
            </li>
            <li data-step="4">
                <span class="step-number">5</span>
                <img src="{{ asset('image/contact-form.png') }}" alt="contact" />
                <span class="step-label">انهاء الطلب</span>
            </li>
            <li data-step="5">
                <span class="step-number">6</span>
                <img src="{{ asset('image/printer.png') }}" alt="printer" />
                <span class="step-label">الطباعة</span>
            </li>
        </ul>
    </div>
    <!-- End Tabs -->

<div class="tabs-content" id="tabs-content">
    {{-- Step 1: Customer --}}
    <div id="step-customer" class="customer tab active">
        <div class="step-header">
            <h2 class="step-title">
                <span class="step-icon">👤</span>
                تحديد العميل
            </h2>
            <p class="step-description">ابحث عن العميل أو أضف عميلاً جديداً</p>
        </div>
        @include('Orders.steps.customer')
    </div>

    {{-- Step 2: Phone --}}
    <div id="step-phone" class="phone tab">
        <div class="step-header">
            <h2 class="step-title">
                <span class="step-icon">📞</span>
                تحديد رقم الهاتف
            </h2>
            <p class="step-description">اختر أو أضف رقم هاتف للعميل</p>
        </div>
        @include('Orders.steps.phone')
        <div class="step-actions">
            <button type="button" class="btn btn-secondary" data-wizard-prev>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>الرجوع</span>
            </button>
        </div>
    </div>

    {{-- Step 3: Address --}}
    <div id="step-address" class="address tab">
        <div class="step-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="step-title">
                    <span class="step-icon">📍</span>
                    تحديد العنوان
                </h2>
                <p class="step-description">اختر عنوان التوصيل أو حدد من المكتب</p>
            </div>
            <div class="address-header-actions">
                <button type="button" class="btn btn-primary" id="addAddressBtn">
                    اضافة عنوان
                </button>
                <button type="button" class="btn btn-secondary" id="officeAddressBtn">
                    من المكتب
            </button>
            </div>
        </div>
        @include('Orders.steps.address')
        <div class="step-actions">
            <button type="button" class="btn btn-secondary" data-wizard-prev>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>الرجوع</span>
            </button>
            <button type="button" class="btn btn-primary" data-wizard-next>
                <span>التالي</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Step 4: Type (parcel / ticket) --}}
    <div id="step-type" class="packages tab">
        <div class="step-header">
            <h2 class="step-title">
                <span class="step-icon">📦</span>
                ارساليات او سفريات
            </h2>
            <p class="step-description">اختر نوع الطلب الذي تريد إنشاءه</p>
        </div>
        @include('Orders.steps.type')
        <div class="step-actions">
            <button type="button" class="btn btn-secondary" data-wizard-prev>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>الرجوع</span>
            </button>
            <button type="button" class="btn btn-primary" data-wizard-next>
                <span>التالي</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Step 5: Form (either parcel or ticket) --}}
    <div id="step-form" class="formS tab">
        <div class="step-header">
            <h2 class="step-title">
                <span class="step-icon">📝</span>
                انهاء الطلب
            </h2>
            <p class="step-description">أكمل تفاصيل الطلب وأرسله</p>
        </div>
        {{-- this will be filled dynamically depending on type --}}
        <div id="orderStepFormContainer">
            <div class="loading-state">
                <div class="spinner"></div>
                <p>جاري التحميل...</p>
            </div>
        </div>
        <div class="step-actions">
            <button type="button" class="btn btn-secondary" data-wizard-prev>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>الرجوع</span>
            </button>
            <button type="button" class="btn btn-primary" id="wizardSubmitBtn">
                <span>ارسال</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M3 10L17 10M10 3L17 10L10 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Step 6: Print --}}
    <div id="step-print" class="formS tab print">
        <div class="step-header">
            <h2 class="step-title">
                <span class="step-icon">🖨️</span>
                الطباعة
            </h2>
            <p class="step-description">تم حفظ الطلب بنجاح. يمكنك طباعته الآن</p>
        </div>
        {{-- we'll render the print ticket/parcel layout here after save --}}
        <div id="orderPrintContainer"></div>

        <div class="step-actions">
            <button type="button" class="btn btn-secondary" data-wizard-prev>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>الرجوع</span>
            </button>
            <button type="button" class="btn btn-outline-primary" id="wizardPrintBtn">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M5 5H15V13H5V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 13H3C2.44772 13 2 13.4477 2 14V16C2 16.5523 2.44772 17 3 17H17C17.5523 17 18 16.5523 18 16V14C18 13.4477 17.5523 13 17 13H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>طباعة</span>
            </button>
        </div>
    </div>
</div>

{{-- Hidden Modal Templates --}}
<template id="customerModalTemplate">
    <div class="modal-header">
        <h5 class="modal-title">اضافة عميل</h5>
        <button type="button" class="btn-close" id="closeCustomerModal" aria-label="Close"></button>
    </div>
    <form id="addCustomerForm">
        <div class="customer-modal-content">
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
</template>

<template id="addressModalTemplate">
    <div class="modal-header">
        <h5 class="modal-title" id="addressModalTitle">اضافة عنوان</h5>
        <button type="button" class="btn-close" id="closeAddressModal" aria-label="Close"></button>
            </div>
    <form id="addressForm">
        <div class="form-group">
            <label for="addressCity">المدينة <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="addressCity" name="city" required>
        </div>
        <div class="form-group">
            <label for="addressArea">المنطقة <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="addressArea" name="area" required>
            </div>
        <div class="form-group">
            <label for="addressStreet">اسم الشارع <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="addressStreet" name="street" required>
        </div>
        <div class="form-group">
            <label for="addressBuilding">رقم المبنى <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="addressBuilding" name="buildingNumber" required>
            </div>
        <div class="form-group">
            <label for="addressInfo">معلومات اضافية</label>
            <textarea class="form-control" id="addressInfo" name="info" rows="3"></textarea>
        </div>
    </form>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelAddressBtn">اغلاق</button>
        <button type="button" class="btn btn-primary" id="submitAddressBtn">اضافة</button>
    </div>
</template>
@endsection
