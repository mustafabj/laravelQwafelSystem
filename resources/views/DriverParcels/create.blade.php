@extends('layouts.app')

@section('page-title', 'إضافة إرسالية سائق')

@section('content')
<div class="driver-parcel-form-container">

    <form id="driverParcelForm" class="driver-parcel-form" action="{{ route('driver-parcels.store') }}" method="POST">
        @csrf

        <!-- Basic and Financial Information Sections - Side by Side -->
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
                        <label for="tripId">
                            <i class="fas fa-bus label-icon"></i>
                            الرحلة <span class="text-danger">*</span>
                        </label>
                        <select name="tripId" id="tripId" required>
                            <option value="">اختر الرحلة</option>
                            @foreach ($trips as $trip)
                                <option value="{{ $trip->tripId }}" 
                                        data-driver-name="{{ $trip->driver->driverName ?? '' }}"
                                        data-destination="{{ $trip->destination }}">
                                    {{ $trip->tripName }} - {{ $trip->driver->driverName ?? '' }} ({{ $trip->destination }})
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
                        <label for="driverName">
                            <i class="fas fa-user label-icon"></i>
                            اسم السائق <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="driverName" id="driverName" required>
                    </div>

                    <div class="form-group">
                        <label for="driverNumber">
                            <i class="fas fa-phone label-icon"></i>
                            رقم السائق <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="driverNumber" id="driverNumber" required>
                    </div>

                    <div class="form-group">
                        <label for="sendTo">
                            <i class="fas fa-map-marker-alt label-icon"></i>
                            الوجهة <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="sendTo" id="sendTo" required>
                    </div>

                    <div class="form-group">
                        <label for="officeId">
                            <i class="fas fa-building label-icon"></i>
                            المكتب <span class="text-danger">*</span>
                        </label>
                        <select name="officeId" id="officeId" required>
                            <option value="">اختر المكتب</option>
                            @foreach ($offices as $office)
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

        <!-- Parcel Details Selection Section -->
        <div class="form-section parcels-selection-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-box section-icon"></i>
                    تفاصيل الإرساليات
                </h3>
            </div>

            <div class="parcels-container">
                <!-- Available Parcels Section (Right) -->
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

                <!-- Selected Parcels Section (Left) -->
                <div class="selected-parcels-section" id="selectedParcelsSection">
                    <div class="parcel-section-header">
                        <h4 class="parcel-section-title">
                            <i class="fas fa-list"></i>
                            الإرساليات المختارة
                        </h4>
                        <span class="selected-count" id="selectedCount">0</span>
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
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                <i class="fas fa-times"></i> إلغاء
            </button>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save"></i> حفظ الإرسالية
            </button>
        </div>
    </form>
</div>
@endsection

