@extends('layouts.app')

@section('page-title', 'إدارة الرحلات')

@section('content')
<div class="trips-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-bus"></i>
            إدارة الرحلات
        </h1>
        <a href="{{ route('trips.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            إضافة رحلة جديدة
        </a>
    </div>

    <div class="trips-list">
        @forelse ($trips as $trip)
            <div class="trip-card">
                <div class="trip-header">
                    <div class="trip-title-section">
                        <h3 class="trip-name">
                            <i class="fas fa-route"></i>
                            {{ $trip->tripName }}
                        </h3>
                        <div class="trip-meta">
                            <span class="trip-destination">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $trip->destination }}
                            </span>
                            <span class="trip-office">
                                <i class="fas fa-building"></i>
                                {{ $trip->office->officeName ?? 'غير محدد' }}
                            </span>
                        </div>
                    </div>
                    <div class="trip-status">
                        @if ($trip->isActive)
                            <span class="status-badge status-active">
                                <i class="fas fa-check-circle"></i>
                                نشط
                            </span>
                        @else
                            <span class="status-badge status-inactive">
                                <i class="fas fa-times-circle"></i>
                                غير نشط
                            </span>
                        @endif
                    </div>
                </div>

                <div class="trip-schedule">
                    <h4 class="schedule-title">
                        <i class="fas fa-calendar-week"></i>
                        الجدول الزمني
                    </h4>
                    <div class="schedule-days">
                        @foreach ($trip->daysOfWeek as $day)
                            <div class="schedule-day">
                                <span class="day-name">{{ $day }}</span>
                                <span class="day-time">
                                    {{ $trip->times[$day] ?? 'غير محدد' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($trip->stopPoints && $trip->stopPoints->count() > 0)
                    <div class="trip-stop-points">
                        <h4 class="schedule-title">
                            <i class="fas fa-map-marked-alt"></i>
                            نقاط التوقف
                        </h4>
                        <div class="stop-points-display">
                            @foreach ($trip->stopPoints as $stopPoint)
                                <div class="stop-point-display-item">
                                    <span class="stop-point-name">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $stopPoint->stopName }}
                                    </span>
                                    <span class="stop-point-time">
                                        <i class="fas fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($stopPoint->arrivalTime)->format('H:i') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($trip->finalArrivalTime)
                    <div class="trip-final-arrival">
                        <h4 class="schedule-title">
                            <i class="fas fa-flag-checkered"></i>
                            وقت الوصول النهائي
                        </h4>
                        <div class="final-arrival-time-display">
                            <i class="fas fa-clock"></i>
                            {{ \Carbon\Carbon::parse($trip->finalArrivalTime)->format('H:i') }}
                        </div>
                    </div>
                @endif

                @if ($trip->notes)
                    <div class="trip-notes">
                        <strong>ملاحظات:</strong>
                        <p>{{ $trip->notes }}</p>
                    </div>
                @endif

                <div class="trip-footer">
                    <div class="trip-info">
                        <span class="trip-created">
                            <i class="fas fa-user-plus"></i>
                            أنشأها: {{ $trip->creator->name ?? 'غير معروف' }}
                        </span>
                        <span class="trip-date">
                            <i class="fas fa-clock"></i>
                            {{ $trip->created_at->format('Y-m-d') }}
                        </span>
                    </div>
                    <div class="trip-actions">
                        <button class="btn btn-sm btn-secondary" onclick="editTrip({{ $trip->tripId }})">
                            <i class="fas fa-edit"></i>
                            تعديل
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTrip({{ $trip->tripId }})">
                            <i class="fas fa-trash"></i>
                            حذف
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-bus"></i>
                <p>لا توجد رحلات</p>
                <a href="{{ route('trips.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    إضافة رحلة جديدة
                </a>
            </div>
        @endforelse
    </div>

    @if ($trips->hasPages())
        <div class="pagination-wrapper">
            {{ $trips->links() }}
        </div>
    @endif
</div>
@endsection

