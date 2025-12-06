<template id="template-step-type">

    <div class="type-selection-container">

        <div class="type-card" data-wizard-type="parcel">
            <div class="type-icon">
                <img src="{{ asset('image/box2.png') }}" alt="parcel">
            </div>
            <h3 class="type-title">ارساليات</h3>
            <p class="type-description">إنشاء ارسالية شحن جديدة</p>
        </div>

        <div class="type-card" data-wizard-type="ticket">
            <div class="type-icon">
                <img src="{{ asset('image/suv-car.png') }}" alt="ticket">
            </div>
            <h3 class="type-title">سفريات</h3>
            <p class="type-description">إنشاء تذكرة سفر جديدة</p>
        </div>

    </div>

</template>
