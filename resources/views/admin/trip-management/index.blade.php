@extends('layouts.app')

@section('page-title', 'إدارة الرحلات والموافقات')

@section('content')
<div class="trip-management-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-route"></i>
            إدارة الرحلات والموافقات على نقاط التوقف
        </h1>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-icon">
                <i class="fas fa-list"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['total'] }}</h3>
                <p>إجمالي الإرساليات</p>
            </div>
        </div>
        <div class="stat-card stat-active">
            <div class="stat-icon">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['active'] }}</h3>
                <p>إرساليات نشطة</p>
            </div>
        </div>
        <div class="stat-card stat-completed">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['completed'] }}</h3>
                <p>مكتملة</p>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('admin.trip-management.index', ['filter' => 'all']) }}" 
           class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
            <i class="fas fa-list"></i>
            الكل ({{ $stats['total'] }})
        </a>
        <a href="{{ route('admin.trip-management.index', ['filter' => 'active']) }}" 
           class="filter-tab {{ $filter === 'active' ? 'active' : '' }}">
            <i class="fas fa-sync-alt"></i>
            نشطة ({{ $stats['active'] }})
        </a>
        <a href="{{ route('admin.trip-management.index', ['filter' => 'not_started']) }}" 
           class="filter-tab {{ $filter === 'not_started' ? 'active' : '' }}">
            <i class="fas fa-clock"></i>
            لم تبدأ ({{ $stats['not_started'] ?? 0 }})
        </a>
        <a href="{{ route('admin.trip-management.index', ['filter' => 'completed']) }}" 
           class="filter-tab {{ $filter === 'completed' ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i>
            مكتملة ({{ $stats['completed'] }})
        </a>
    </div>

    <div class="driver-parcels-list">
        @forelse($driverParcels as $driverParcel)
            <div class="driver-parcel-card">
                <div class="driver-parcel-header">
                    <div class="driver-parcel-title">
                        <h3>
                            <i class="fas fa-box"></i>
                            إرسالية #{{ $driverParcel['parcelNumber'] }}
                        </h3>
                        <div class="driver-parcel-meta">
                            <span class="meta-item">
                                <i class="fas fa-user"></i>
                                {{ $driverParcel['driverName'] }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-building"></i>
                                {{ $driverParcel['officeName'] }}
                            </span>
                            @if($driverParcel['showTripName'])
                                <span class="meta-item">
                                    <i class="fas fa-route"></i>
                                    من رحلة: {{ $driverParcel['tripName'] }}
                                </span>
                            @endif
                            @if($driverParcel['showTripDate'])
                                <span class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    تاريخ الإرسالية: {{ $driverParcel['tripDate'] }}
                                </span>
                            @endif
                            @if($driverParcel['showParcelDate'])
                                <span class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    تاريخ الإرسالية: {{ $driverParcel['parcelDate'] }}
                                </span>
                            @endif
                            <span class="meta-item">
                                <i class="fas fa-info-circle"></i>
                                {{ $driverParcel['statusText'] }}
                            </span>
                        </div>
                    </div>
                    <div class="driver-parcel-status">
                        <span class="badge {{ $driverParcel['statusBadge']['class'] }}">
                            <i class="fas {{ $driverParcel['statusBadge']['icon'] }}"></i>
                            {{ $driverParcel['statusBadge']['text'] }}
                        </span>
                    </div>
                </div>

                <!-- Progress Timeline -->
                <div class="progress-timeline">
                    <div class="timeline-header">
                        <h4 class="section-title">
                            <i class="fas fa-list-ol"></i>
                            مسار الإرسالية
                        </h4>
                    </div>

                    <div class="timeline-container">
                        @foreach($driverParcel['stopPoints'] as $stopPoint)
                            <div class="timeline-item {{ $stopPoint['hasArrived'] ? 'completed' : '' }} {{ $stopPoint['hasDelay'] ? 'delayed' : '' }}" 
                                 data-stop-point-id="{{ $stopPoint['stopPointId'] }}"
                                 data-arrival-id="{{ $stopPoint['arrival']['arrivalId'] ?? '' }}">
                                
                                <div class="timeline-line"></div>
                                <div class="timeline-dot">
                                    @if($stopPoint['hasArrived'])
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-circle"></i>
                                    @endif
                                </div>

                                <div class="timeline-content">
                                    <div class="timeline-header-info">
                                        <div class="stop-point-info">
                                            <h5 class="stop-point-name">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $stopPoint['stopName'] }}
                                            </h5>
                                            <div class="stop-point-meta">
                                                @if($stopPoint['showExpectedTime'])
                                                    <span class="meta-item">
                                                        <i class="fas {{ $stopPoint['expectedTimeIcon'] }}"></i>
                                                        {{ $stopPoint['expectedTimeLabel'] }}: {{ $stopPoint['expectedTimeDisplay'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="arrival-status-badge">
                                            @if($stopPoint['hasArrived'])
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i>
                                                    وصل
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-minus"></i>
                                                    لم يصل بعد
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($stopPoint['hasArrived'])
                                        <div class="arrival-details">
                                            @if($stopPoint['showArrivedAt'])
                                                <div class="detail-item">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>وصل في: {{ $stopPoint['arrival']['arrivedAt'] }}</span>
                                                </div>
                                            @endif

                                            @if($stopPoint['showOnTime'])
                                                <div class="detail-item">
                                                    <i class="fas {{ $stopPoint['arrival']['onTime'] ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-warning' }}"></i>
                                                    <span class="{{ $stopPoint['arrival']['onTime'] ? 'text-success' : 'text-warning' }}">{{ $stopPoint['arrival']['onTimeText'] }}</span>
                                                </div>
                                            @endif

                                            @if($stopPoint['showAutoApprovedMessage'])
                                                <div class="auto-approved-message">
                                                    <i class="fas fa-check-circle"></i>
                                                    <strong>تمت الموافقة التلقائية</strong>
                                                    <p>تمت الموافقة تلقائياً على نقطة البداية</p>
                                                </div>
                                            @endif

                                            @if($stopPoint['showDelay'])
                                                <div class="notes-section" style="background: #fff7ed; border-right-color: var(--accent-color);">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <strong>سبب التأخير:</strong>
                                                    <p>{{ $stopPoint['arrival']['delayReason'] }}</p>
                                                    @if($stopPoint['arrival']['delayDuration'])
                                                        <p style="margin-top: 8px; margin-bottom: 0;">
                                                            <strong>مدة التأخير:</strong> {{ $stopPoint['arrival']['delayDuration'] }} دقيقة (تم إضافة هذا الوقت للنقاط اللاحقة)
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($stopPoint['showAdminComment'])
                                                <div class="notes-section">
                                                    <i class="fas fa-sticky-note"></i>
                                                    <strong>ملاحظات:</strong>
                                                    <p>{{ $stopPoint['arrival']['adminComment'] }}</p>
                                                </div>
                                            @endif

                                            @if($stopPoint['showApprovedAt'])
                                                <div class="detail-item">
                                                    <i class="fas fa-calendar-check"></i>
                                                    <span>تاريخ الوصول: {{ $stopPoint['arrival']['approvedAt'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="no-arrival-info">
                                            <i class="fas fa-info-circle"></i>
                                            <span>لم يصل السائق إلى هذه النقطة بعد</span>
                                        </div>
                                        <div class="arrival-actions">
                                            <button class="btn btn-info btn-sm edit-btn" 
                                                    data-stop-point-id="{{ $stopPoint['stopPointId'] }}"
                                                    data-driver-parcel-id="{{ $driverParcel['parcelId'] }}"
                                                    @if($stopPoint['calculatedExpectedTime'])
                                                    data-expected-time="{{ str_replace(' ', 'T', $stopPoint['calculatedExpectedTime']) }}"
                                                    @endif>
                                                <i class="fas fa-edit"></i> تعديل
                                            </button>
                                            @if($stopPoint['shouldShowMarkArrivedButton'])
                                                <button class="btn btn-success btn-sm mark-arrived-btn" 
                                                        data-driver-parcel-id="{{ $driverParcel['parcelId'] }}"
                                                        data-stop-point-id="{{ $stopPoint['stopPointId'] }}"
                                                        data-stop-name="{{ $stopPoint['stopName'] }}">
                                                    <i class="fas fa-check-circle"></i> وصل إلى النقطة
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>لا توجد إرساليات</h3>
                <p>لا توجد إرساليات متاحة حالياً</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modals -->
@foreach($driverParcels as $driverParcel)
    @foreach($driverParcel['stopPoints'] as $stopPoint)
        @if($stopPoint['showDelayModal'])
            <!-- Delay Modal -->
            <div class="modal" id="delayModal{{ $stopPoint['arrival']['arrivalId'] }}">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>تسجيل التأخير</h4>
                        <button class="modal-close" data-modal-id="delayModal{{ $stopPoint['arrival']['arrivalId'] }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form class="delay-form" data-arrival-id="{{ $stopPoint['arrival']['arrivalId'] }}">
                        @csrf
                        <div class="form-group">
                            <label>سبب التأخير (مطلوب):</label>
                            <textarea name="delayReason" rows="3" required placeholder="أدخل سبب التأخير (سيظهر للعميل)">{{ $stopPoint['arrival']['delayReason'] ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>مدة التأخير (بالدقائق) (مطلوب):</label>
                            <input type="number" name="delayDuration" min="1" required placeholder="مثال: 30" value="{{ $stopPoint['arrival']['delayDuration'] ?? '' }}" class="form-control">
                            <small style="color: var(--gray-600); margin-top: 5px; display: block;">
                                سيتم إضافة هذا الوقت تلقائياً لجميع النقاط اللاحقة في الرحلة
                            </small>
                        </div>
                        <div class="form-group">
                            <label>ملاحظات إضافية (اختياري:</label>
                            <textarea name="comment" rows="2" placeholder="ملاحظات إضافية">{{ $stopPoint['arrival']['adminComment'] ?? '' }}</textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-check"></i> تسجيل التأخير
                            </button>
                            <button type="button" class="btn btn-secondary" data-modal-id="delayModal{{ $stopPoint['arrival']['arrivalId'] }}">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        
        @if($stopPoint['showEditModal'])
            <!-- Edit Modal -->
            <div class="modal" id="{{ $stopPoint['modalId'] }}">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>تعديل وقت الوصول والملاحظات</h4>
                        <button class="modal-close" data-modal-id="{{ $stopPoint['modalId'] }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form class="edit-form" 
                          data-arrival-id="{{ $stopPoint['hasArrived'] && $stopPoint['arrival'] ? $stopPoint['arrival']['arrivalId'] : '' }}"
                          data-driver-parcel-id="{{ $driverParcel['parcelId'] }}"
                          data-stop-point-id="{{ $stopPoint['stopPointId'] }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label>وقت الوصول المتوقع:</label>
                            <input type="datetime-local" 
                                   name="expectedArrivalTime" 
                                   value="{{ $stopPoint['expectedTimeForModal'] }}"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>ملاحظات (ستظهر للعميل):</label>
                            <textarea name="adminComment" rows="4" placeholder="أضف ملاحظات للعميل (اختياري)">{{ $stopPoint['arrival']['adminComment'] ?? '' }}</textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ
                            </button>
                            <button type="button" class="btn btn-secondary" data-modal-id="{{ $stopPoint['modalId'] }}">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endforeach

@if(config('broadcasting.default') === 'pusher')
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">
@endif
@endsection
