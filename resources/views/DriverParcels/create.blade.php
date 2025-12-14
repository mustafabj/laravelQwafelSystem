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
            <div class="progress-fill" id="driverParcelProgressFill" style="width: 25%"></div>
        </div>
        <div class="progress-steps">
            <span class="progress-text">الخطوة <span id="driverParcelCurrentStep">1</span> من <span id="driverParcelTotalSteps">4</span></span>
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
        </ul>
    </div>

    <form id="driverParcelForm" class="driver-parcel-form" action="{{ route('driver-parcels.store') }}" method="POST">
        @csrf
        <input type="hidden" name="driverId" id="driverId">
        <input type="hidden" name="officeId" id="hiddenOfficeId">

        <div class="tabs-content" id="driver-parcel-tabs-content">
            <!-- Step 1: Driver Selection -->
            <div id="step-driver" class="driver tab active">
                <div class="step-header">
                    <h2 class="step-title">
                        <span class="step-icon">👤</span>
                        تحديد السائق
                    </h2>
                    <p class="step-description">ابحث عن السائق أو أضف سائقاً جديداً</p>
                </div>
                
                <div class="driver-search-container">
                    <div class="search-header-actions">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="search-driver" placeholder="ابحث بالاسم أو رقم الهاتف..." autocomplete="off">
                            <div class="search-loading" id="driverSearchLoading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="addDriver">
                            <i class="fas fa-plus"></i>
                            إضافة سائق جديد
                        </button>
                    </div>
                    
                    <div class="selected-driver-info" id="selectedDriverInfo" style="display: none;"></div>
                    
                    <div class="driver-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>اسم السائق</th>
                                    <th>رقم الهاتف</th>
                                    <th>تعديل</th>
                                </tr>
                            </thead>
                            <tbody id="driverBody">
                                @include('Drivers.partials.search-states', ['state' => 'initial'])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Step 2: Trip Selection -->
            <div id="step-trip" class="trip tab">
                <div class="step-header">
                    <h2 class="step-title">
                        <span class="step-icon">🚌</span>
                        اختيار الرحلة
                    </h2>
                    <p class="step-description">اختر الرحلة المرتبطة بهذه الإرسالية</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="tripId">
                            <i class="fas fa-bus label-icon"></i>
                            الرحلة <span class="text-danger">*</span>
                        </label>
                        <select name="tripId" id="tripId" required>
                            <option value="">اختر الرحلة</option>
                            @foreach ($trips as $trip)
                                <option value="{{ $trip->tripId }}" 
                                        data-destination="{{ $trip->destination }}"
                                        data-office-id="{{ $trip->officeId }}">
                                    {{ $trip->tripName }} ({{ $trip->destination }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tripDate">
                            <i class="fas fa-calendar label-icon"></i>
                            تاريخ الرحلة <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tripDate" id="tripDate" required>
                    </div>

                    <div class="form-group">
                        <label for="sendTo">
                            <i class="fas fa-map-marker-alt label-icon"></i>
                            الوجهة
                        </label>
                        <input type="text" name="sendTo" id="sendTo" readonly class="readonly-input">
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" data-wizard-prev>
                        <i class="fas fa-arrow-right"></i>
                        <span>الرجوع</span>
                    </button>
                    <button type="button" class="btn btn-primary" data-wizard-next>
                        <span>التالي</span>
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Basic & Financial Information -->
            <div id="step-info" class="info tab">
                <div class="step-header">
                    <h2 class="step-title">
                        <span class="step-icon">📋</span>
                        المعلومات الأساسية والمالية
                    </h2>
                    <p class="step-description">أكمل المعلومات الأساسية والمالية للإرسالية</p>
                </div>

                <div class="form-sections-container">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-clipboard-list section-icon"></i>
                                المعلومات الأساسية
                            </h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="parcelNumber">
                                    <i class="fas fa-hashtag label-icon"></i>
                                    رقم الإرسالية
                                </label>
                                <input type="number" name="parcelNumber" id="parcelNumber" value="{{ $nextParcelNumber }}" required readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="driverName">
                                    <i class="fas fa-user label-icon"></i>
                                    اسم السائق <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="driverName" id="driverName" required readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="driverNumber">
                                    <i class="fas fa-phone label-icon"></i>
                                    رقم السائق <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="driverNumber" id="driverNumber" required readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="officeId">
                                    <i class="fas fa-building label-icon"></i>
                                    المكتب
                                </label>
                                <select name="officeId" id="officeId" class="form-select" disabled>
                                    <option value="">اختر المكتب</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->officeId }}">{{ $office->officeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-money-bill-wave section-icon"></i>
                                المعلومات المالية
                            </h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cost">
                                    <i class="fas fa-dollar-sign label-icon"></i>
                                    التكلفة
                                </label>
                                <input type="number" name="cost" id="cost" step="0.01" min="0" value="0">
                            </div>

                            <div class="form-group">
                                <label for="paid">
                                    <i class="fas fa-credit-card label-icon"></i>
                                    المدفوع
                                </label>
                                <input type="number" name="paid" id="paid" step="0.01" min="0" value="0">
                            </div>

                            <div class="form-group">
                                <label for="costRest">
                                    <i class="fas fa-chart-line label-icon"></i>
                                    المتبقي
                                </label>
                                <input type="number" name="costRest" id="costRest" step="0.01" min="0" value="0" readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="currency">
                                    <i class="fas fa-exchange-alt label-icon"></i>
                                    العملة
                                </label>
                                <select name="currency" id="currency">
                                    <option value="IQD" selected>دينار عراقي</option>
                                    <option value="USD">دولار</option>
                                    <option value="EUR">يورو</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" data-wizard-prev>
                        <i class="fas fa-arrow-right"></i>
                        <span>الرجوع</span>
                    </button>
                    <button type="button" class="btn btn-primary" data-wizard-next>
                        <span>التالي</span>
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- Step 4: Parcels Selection -->
            <div id="step-parcels" class="parcels tab">
                <div class="step-header">
                    <h2 class="step-title">
                        <span class="step-icon">📦</span>
                        اختيار الإرساليات
                    </h2>
                    <p class="step-description">اختر الإرساليات المراد إضافتها لهذه الإرسالية</p>
                </div>

                <!-- Summary Section -->
                <div class="parcel-summary-card" id="parcelSummaryCard" style="display: none;">
                    <div class="summary-header">
                        <h3 class="summary-title">
                            <i class="fas fa-clipboard-check"></i>
                            ملخص البيانات المختارة
                        </h3>
                    </div>
                    <div class="summary-content">
                        <div class="summary-section">
                            <h4 class="summary-section-title">
                                <i class="fas fa-user-tie"></i>
                                معلومات السائق
                            </h4>
                            <div class="summary-items">
                                <div class="summary-item">
                                    <span class="summary-label">اسم السائق:</span>
                                    <span class="summary-value" id="summaryDriverName">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">رقم الهاتف:</span>
                                    <span class="summary-value" id="summaryDriverPhone">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="summary-section">
                            <h4 class="summary-section-title">
                                <i class="fas fa-route"></i>
                                معلومات الرحلة
                            </h4>
                            <div class="summary-items">
                                <div class="summary-item">
                                    <span class="summary-label">اسم الرحلة:</span>
                                    <span class="summary-value" id="summaryTripName">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">الوجهة:</span>
                                    <span class="summary-value" id="summaryDestination">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">تاريخ الرحلة:</span>
                                    <span class="summary-value" id="summaryTripDate">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="summary-section">
                            <h4 class="summary-section-title">
                                <i class="fas fa-info-circle"></i>
                                المعلومات الأساسية
                            </h4>
                            <div class="summary-items">
                                <div class="summary-item">
                                    <span class="summary-label">رقم الإرسالية:</span>
                                    <span class="summary-value" id="summaryParcelNumber">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">المكتب:</span>
                                    <span class="summary-value" id="summaryOffice">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="summary-section">
                            <h4 class="summary-section-title">
                                <i class="fas fa-money-bill-wave"></i>
                                المعلومات المالية
                            </h4>
                            <div class="summary-items">
                                <div class="summary-item">
                                    <span class="summary-label">التكلفة:</span>
                                    <span class="summary-value" id="summaryCost">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">المدفوع:</span>
                                    <span class="summary-value" id="summaryPaid">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">المتبقي:</span>
                                    <span class="summary-value" id="summaryCostRest">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">العملة:</span>
                                    <span class="summary-value" id="summaryCurrency">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="parcels-container">
                    <!-- Available Parcels Section -->
                    <div class="parcel-search-section">
                        <div class="parcel-section-header">
                            <h4 class="parcel-section-title">
                                <i class="fas fa-search"></i>
                                اختر من الإرساليات المتاحة
                            </h4>
                        </div>
                        
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   id="parcelDetailsSearch" 
                                   placeholder="ابحث برقم الإرسالية، اسم العميل، أو وصف المحتويات..."
                                   autocomplete="off">
                            <div class="search-loading" id="searchLoading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>

                        <div class="available-parcels-list" id="availableParcelsList">
                            <div class="empty-state" id="emptySearchState">
                                <i class="fas fa-search"></i>
                                <p>ابحث عن الإرساليات المتاحة لإضافتها</p>
                                <small>يمكنك البحث برقم الإرسالية، اسم العميل، أو وصف المحتويات</small>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Parcels Section -->
                    <div class="selected-parcels-section" id="selectedParcelsSection">
                        <div class="parcel-section-header">
                            <h4 class="parcel-section-title">
                                <i class="fas fa-list"></i>
                                الإرساليات المختارة
                            </h4>
                        </div>
                        
                        <div class="selected-parcels-list" id="selectedParcelsList">
                            <div class="empty-state-selected" id="emptySelectedState">
                                <i class="fas fa-hand-pointer"></i>
                                <p>اسحب الإرساليات من القائمة اليسرى</p>
                                <small>أو اضغط على زر "إضافة" لإضافة الإرسالية</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" data-wizard-prev>
                        <i class="fas fa-arrow-right"></i>
                        <span>الرجوع</span>
                    </button>
                    <button type="button" class="btn btn-primary" id="driverParcelSubmitBtn">
                        <i class="fas fa-save"></i>
                        <span>حفظ الإرسالية</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
