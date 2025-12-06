<template id="template-step-phone">

    <div class="phone-step-container">

        <div class="customer-name-display">
            <div class="name-field">
                <label for="wizardPhoneFName">الاسم الاول</label>
                <input type="text" id="wizardPhoneFName" readonly>
            </div>

            <div class="name-field">
                <label for="wizardPhoneLName">الاسم الثاني</label>
                <input type="text" id="wizardPhoneLName" readonly>
            </div>
        </div>

        <div class="phone-numbers-container" id="wizardPhoneNumbersContainer"></div>

        <template id="template-phone-item">
            @include('Orders.templates.partials.phone-item')
        </template>

        <button type="button" class="btn-add-phone" id="wizardAddPhoneBtn">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>هاتف جديد</span>
        </button>

    </div>

</template>
