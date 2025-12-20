@extends('layouts.app')

@section('page-title', 'إضافة إرسالية سائق')

@section('content')
<!-- Driver Modal Template -->
<template id="driverModalTemplate">
    @include('Drivers.partials.driver_modal')
</template>
<div class="driver-parcel-wizard-container">
    <!-- Progress Bar -->
    <div class="wizard-progress noPrint">
        <div class="progress-bar">
            <div class="progress-fill" id="driverParcelProgressFill" style="width: 20%"></div>
        </div>
        <div class="progress-steps">
            <span class="progress-text">الخطوة <span id="driverParcelCurrentStep">1</span> من <span id="driverParcelTotalSteps">5</span></span>
        </div>
    </div>

    <!-- Tabs -->
    <div class="driver-parcel-tabs noPrint">
        <ul>
            <li class="active" data-step="0">
                <span class="step-number">1</span>
                <i class="fas fa-user-tie"></i>
                <span class="step-label">اختيار السائق</span>
            </li>
            <li data-step="1">
                <span class="step-number">2</span>
                <i class="fas fa-bus"></i>
                <span class="step-label">اختيار الرحلة</span>
            </li>
            <li data-step="2">
                <span class="step-number">3</span>
                <i class="fas fa-clipboard-list"></i>
                <span class="step-label">المعلومات الأساسية</span>
            </li>
            <li data-step="3">
                <span class="step-number">4</span>
                <i class="fas fa-box"></i>
                <span class="step-label">اختيار الإرساليات</span>
            </li>
            <li data-step="4">
                <span class="step-number">5</span>
                <i class="fas fa-print"></i>
                <span class="step-label">المراجعة والطباعة</span>
            </li>
        </ul>
    </div>

    <form id="driverParcelForm" class="driver-parcel-form" action="{{ route('driver-parcels.store') }}" method="POST">
        @csrf
        <input type="hidden" name="driverId" id="driverId">
        <input type="hidden" name="officeId" id="hiddenOfficeId">

        <div class="tabs-content" id="driver-parcel-tabs-content">
            @include('DriverParcels.steps.driver')
            @include('DriverParcels.steps.trip')
            @include('DriverParcels.steps.info')
            @include('DriverParcels.steps.parcels')
            @include('DriverParcels.steps.print')
        </div>
    </form>
</div>


@include('DriverParcels.Templates.available-parcel')
@include('DriverParcels.Templates.selected-parcel')

<template id="tpl-available-parcel">
    <div class="parcel-item">
        <div class="parcel-item-header">
            <strong data-bind="parcelNumber"></strong>
            <span data-bind="customerName"></span>
            <span class="available-badge" data-bind="availableText"></span>
        </div>

        <div class="parcel-item-body">
            <p data-bind="description"></p>

            <div class="parcel-actions">
                <button class="btn-qty minus">−</button>
                <input type="number" class="quantity-input" min="1" value="1">
                <button class="btn-qty plus">+</button>

                <button type="button" class="btn btn-add-parcel">
                    إضافة
                </button>
            </div>
        </div>
    </div>
</template>

<template id="tpl-selected-parcel-group">
    <div class="parcel-group">
        <div class="parcel-group-header">
            <i class="fas fa-grip-vertical drag-handle"></i>
            <strong data-bind="parcelNumber"></strong>
            <span data-bind="customerName"></span>
        </div>

        <div class="parcel-group-items"></div>
    </div>
</template>

<template id="tpl-selected-parcel-item">
    <div class="selected-parcel-item">
        <span data-bind="description"></span>

        <div class="qty-controls">
            <button class="btn-qty minus">−</button>
            <input type="number" class="quantity-edit-input" min="1">
            <button class="btn-qty plus">+</button>
        </div>

        <button class="btn-remove-parcel">✕</button>
    </div>
</template>

@endsection
