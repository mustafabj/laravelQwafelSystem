@extends('layouts.app')

@section('page-title', 'إضافة رحلة جديدة')

@section('content')
<div class="trip-form-container">
    <h1 class="form-title">
        <i class="fas fa-bus"></i>
        إضافة رحلة جديدة
    </h1>

    <form id="tripForm" class="trip-form" action="{{ route('trips.store') }}" method="POST">
        @csrf

        <!-- Basic Information Section -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-info-circle section-icon"></i>
                    المعلومات الأساسية
                </h3>
                <span class="section-badge required-badge">مطلوب</span>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="tripName">
                        <i class="fas fa-signature label-icon"></i>
                        اسم الرحلة <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="tripName" id="tripName" required placeholder="مثال: رحلة بغداد - الموصل">
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

                <div class="form-group">
                    <label for="destination">
                        <i class="fas fa-map-marker-alt label-icon"></i>
                        الوجهة <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="destination" id="destination" required placeholder="مثال: بغداد">
                </div>
            </div>
        </div>

        <!-- Schedule Section -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-calendar-week section-icon"></i>
                    الجدول الزمني
                </h3>
                <span class="section-badge required-badge">مطلوب</span>
            </div>
            <div class="schedule-container">
                <div class="days-selection">
                    <label class="days-label">
                        <i class="fas fa-calendar-check"></i>
                        أيام الأسبوع <span class="text-danger">*</span>
                    </label>
                    <div class="days-checkboxes" id="daysCheckboxes">
                        @php
                            $days = ['Sunday' => 'الأحد', 'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء', 
                                     'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 
                                     'Saturday' => 'السبت'];
                        @endphp
                        @foreach ($days as $key => $label)
                            <label class="day-checkbox-label">
                                <input type="checkbox" name="daysOfWeek[]" value="{{ $key }}" class="day-checkbox" data-day="{{ $key }}">
                                <span class="day-checkbox-text">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="times-container" id="timesContainer">
                    <!-- Times will be added dynamically based on selected days -->
                </div>
            </div>
        </div>

        <!-- Stop Points Section -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-map-marked-alt section-icon"></i>
                    نقاط التوقف ووقت الوصول النهائي
                </h3>
                <span class="section-badge">اختياري</span>
            </div>
            <div class="stop-points-container">
                <div class="stop-points-controls">
                    <button type="button" class="btn btn-secondary" id="addStopPointBtn">
                        <i class="fas fa-plus"></i>
                        إضافة نقطة توقف
                    </button>
                </div>
                <div class="stop-points-list" id="stopPointsList">
                    <!-- Stop points will be added here dynamically -->
                </div>
                
                <div class="final-arrival-time-group">
                    <label for="finalArrivalTime">
                        <i class="fas fa-flag-checkered label-icon"></i>
                        وقت الوصول النهائي للوجهة
                    </label>
                    <input type="time" name="finalArrivalTime" id="finalArrivalTime" placeholder="HH:MM">
                    <small class="form-hint">الوقت المتوقع للوصول إلى الوجهة النهائية</small>
                </div>
            </div>
        </div>

        <!-- Additional Information Section -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-notes-medical section-icon"></i>
                    معلومات إضافية
                </h3>
                <span class="section-badge">اختياري</span>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="isActive">
                        <i class="fas fa-toggle-on label-icon"></i>
                        حالة الرحلة
                    </label>
                    <label class="switch">
                        <input type="checkbox" name="isActive" id="isActive" value="1" checked>
                        <span class="slider"></span>
                        <span class="switch-label">نشط</span>
                    </label>
                </div>

                <div class="form-group full-width">
                    <label for="notes">
                        <i class="fas fa-sticky-note label-icon"></i>
                        ملاحظات
                    </label>
                    <textarea name="notes" id="notes" rows="4" placeholder="أي ملاحظات إضافية حول الرحلة..."></textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                <i class="fas fa-times"></i>
                إلغاء
            </button>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save"></i>
                حفظ الرحلة
            </button>
        </div>
    </form>
</div>
@endsection

