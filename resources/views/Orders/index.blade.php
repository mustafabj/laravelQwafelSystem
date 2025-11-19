@extends('layouts.app')

@section('content')
<div id="orderStepper" class="bs-stepper tabs" data-wizard="order">
    {{-- Stepper header --}}
    <div class="bs-stepper-header">

        <div class="step active" data-target="#step-customer">
            <button type="button" class="step-trigger">
                <img src="{{ asset('image/user2.png') }}" alt="user" />
                <span class="bs-stepper-label">تحديد العميل</span>
            </button>
        </div>
    
        <div class="step" data-target="#step-phone">
            <button type="button" class="step-trigger">
                <img src="{{ asset('image/phone-call.png') }}" alt="phone" />
                <span class="bs-stepper-label">تحديد رقم الهاتف</span>
            </button>
        </div>
    
        <div class="step" data-target="#step-address">
            <button type="button" class="step-trigger">
                <img src="{{ asset('image/pin.png') }}" alt="pin" />
                <span class="bs-stepper-label">تحديد العنوان</span>
            </button>
        </div>
    
        <div class="step" data-target="#step-type">
            <button type="button" class="step-trigger">
                <img src="{{ asset('image/box.png') }}" alt="box" />
                <span class="bs-stepper-label">ارساليات او سفريات</span>
            </button>
        </div>
    
        <div class="step" data-target="#step-form">
            <button type="button" class="step-trigger">
                <img src="{{ asset('image/contact-form.png') }}" alt="contact" />
                <span class="bs-stepper-label">انهاء الطلب</span>
            </button>
        </div>
    
        <div class="step" data-target="#step-print">
            <button type="button" class="step-trigger">
                <img src="{{ asset('image/printer.png') }}" alt="printer" />
                <span class="bs-stepper-label">الطباعة</span>
            </button>
        </div>
    
    </div>
    
    {{-- Stepper content --}}
    <div class="bs-stepper-content tabs-content" id="tabs-content">
        {{-- Step 1: Customer --}}
        <div id="step-customer" class="content active dstepper-block tab">
            @include('Orders.steps.customer')
        
            <div class="mt-3 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" data-bs-stepper-next>التالي</button>
            </div>
        </div>

        {{-- Step 2: Phone --}}
        <div id="step-phone" class="content tab">
            @include('Orders.steps.phone')
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-wizard-prev>الرجوع</button>
                <button type="button" class="btn btn-primary" data-wizard-next>التالي</button>
            </div>
        </div>

        {{-- Step 3: Address --}}
        <div id="step-address" class="content tab">
            @include('Orders.steps.address')
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-wizard-prev>الرجوع</button>
                <button type="button" class="btn btn-primary" data-wizard-next>التالي</button>
            </div>
        </div>

        {{-- Step 4: Type (parcel / ticket) --}}
        <div id="step-type" class="content tab">
            @include('Orders.steps.type')
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-wizard-prev>الرجوع</button>
                <button type="button" class="btn btn-primary" data-wizard-next>التالي</button>
            </div>
        </div>

        {{-- Step 5: Form (either parcel or ticket) --}}
        <div id="step-form" class="content tab">
            {{-- this will be filled dynamically depending on type --}}
            <div id="orderStepFormContainer"></div>
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-wizard-prev>الرجوع</button>
                <button type="button" class="btn btn-primary" id="wizardSubmitBtn">
                    ارسال
                </button>
            </div>
        </div>

        {{-- Step 6: Print --}}
        <div id="step-print" class="content">
            {{-- we’ll render the print ticket/parcel layout here after save --}}
            <div id="orderPrintContainer"></div>

            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-wizard-prev>الرجوع</button>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    طباعة
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
