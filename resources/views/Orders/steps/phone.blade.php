<div class="phone-step-container">
    <div class="customer-name-display">
        <div class="name-field">
            <label for="fname">الاسم الاول</label>
            <input type="text" id="fname" readonly />
        </div>
        <div class="name-field">
            <label for="lname">الاسم الثاني</label>
            <input type="text" id="lname" readonly />
        </div>
    </div>
    
    <div class="phone-numbers-container" id="phoneNumbersContainer">
        <!-- Phone numbers will be dynamically added here -->
    </div>
    
    {{-- Hidden template for phone items --}}
    <template id="phoneItemTemplate">
        @include('Orders.partials.phone_item', ['phoneNumber' => '', 'phoneIndex' => 0])
    </template>
    
    <button type="button" class="btn-add-phone" id="addPhoneBtn">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>هاتف جديد</span>
    </button>
</div>