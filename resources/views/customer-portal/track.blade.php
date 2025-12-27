<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تتبع الإرسالية #{{ $driverParcel->parcelNumber }} - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-route="customer-portal.track" style="font-family: 'Open Sans', sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
    <div class="tracking-container">
        <div class="tracking-header">
            <div>
                <h1><i class="fas fa-box"></i> تتبع الإرسالية #{{ $driverParcel->parcelNumber }}</h1>
            </div>
            <div class="tracking-header-actions">
                <a href="{{ route('customer-portal.dashboard') }}" class="tracking-btn">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
                <form method="POST" action="{{ route('customer-portal.logout') }}" style="display: inline-block;">
                    @csrf
                    <button type="submit" class="tracking-btn tracking-btn-logout">
                        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>

        <div class="tracking-parcel-details">
            <h2>معلومات الإرسالية</h2>
            
            <div class="tracking-detail-row">
                <span class="tracking-detail-label">رقم الإرسالية:</span>
                <span class="tracking-detail-value"><strong>#{{ $driverParcel->parcelNumber }}</strong></span>
            </div>
            
            <div class="tracking-detail-row">
                <span class="tracking-detail-label">حالة إرساليتك:</span>
                <span class="tracking-detail-value">
                    <span class="tracking-status-badge tracking-status-{{ $effectiveStatus }}">
                        @if($effectiveStatus === 'pending')
                            قيد الانتظار
                        @elseif($effectiveStatus === 'in_transit')
                            في الطريق
                        @elseif($effectiveStatus === 'arrived')
                            وصلت
                        @elseif($effectiveStatus === 'delivered')
                            تم التسليم
                        @endif
                    </span>
                </span>
            </div>
            
            @if($driverParcel->trip)
                <div class="tracking-detail-row">
                    <span class="tracking-detail-label">الرحلة:</span>
                    <span class="tracking-detail-value">{{ $driverParcel->trip->tripName }}</span>
                </div>
            @endif
            
            @if($driverParcel->office)
                <div class="tracking-detail-row">
                    <span class="tracking-detail-label">المكتب:</span>
                    <span class="tracking-detail-value">{{ $driverParcel->office->officeName }}</span>
                </div>
            @endif
            
            @if($driverParcel->tripDate)
                <div class="tracking-detail-row">
                    <span class="tracking-detail-label">تاريخ الرحلة:</span>
                    <span class="tracking-detail-value">{{ $driverParcel->tripDate->format('Y-m-d') }}</span>
                </div>
            @endif
            
            @if($driverParcel->parcelDate)
                <div class="tracking-detail-row">
                    <span class="tracking-detail-label">تاريخ الإنشاء:</span>
                    <span class="tracking-detail-value">{{ \Carbon\Carbon::parse($driverParcel->parcelDate)->format('Y-m-d H:i') }}</span>
                </div>
            @endif
            
            @if($driverParcel->arrivedAt)
                <div class="tracking-detail-row">
                    <span class="tracking-detail-label">تاريخ الوصول:</span>
                    <span class="tracking-detail-value">{{ $driverParcel->arrivedAt->format('Y-m-d H:i') }}</span>
                </div>
            @endif
            
            @if($driverParcel->delayReason)
                <div class="tracking-detail-row">
                    <span class="tracking-detail-label">سبب التأخير:</span>
                    <span class="tracking-detail-value" style="color: #dc3545;">{{ $driverParcel->delayReason }}</span>
                </div>
            @endif
        </div>

        <div class="tracking-timeline">
            <h2>سجل التتبع</h2>
            
            @if($trackingHistory->count() > 0)
                @php
                    $lastStatus = null;
                @endphp
                @foreach($trackingHistory as $index => $tracking)
                    @php
                        // Only show "arrived" if all customer items arrived
                        $showThisStatus = true;
                        if ($tracking->status === 'arrived' && !$allCustomerItemsArrived) {
                            $showThisStatus = false;
                        }
                        // Track the last shown status
                        if ($showThisStatus) {
                            $lastStatus = $tracking->status;
                        }
                    @endphp
                    @if($showThisStatus)
                        <div class="tracking-timeline-item {{ $index === 0 && $lastStatus === $tracking->status ? 'active' : '' }}">
                            <div class="tracking-timeline-dot"></div>
                            <div class="tracking-timeline-content">
                                <div class="tracking-timeline-status">
                                    @if($tracking->status === 'pending')
                                        قيد الانتظار
                                    @elseif($tracking->status === 'in_transit')
                                        في الطريق
                                    @elseif($tracking->status === 'arrived')
                                        وصلت جميع العناصر
                                    @elseif($tracking->status === 'delivered')
                                        تم التسليم
                                    @endif
                                </div>
                                @if($tracking->description)
                                    <div class="tracking-timeline-description">{{ $tracking->description }}</div>
                                @endif
                                @if($tracking->location)
                                    <div class="tracking-timeline-location">
                                        <i class="fas fa-map-marker-alt"></i> {{ $tracking->location }}
                                    </div>
                                @endif
                                <div class="tracking-timeline-date">
                                    <i class="fas fa-clock"></i> {{ $tracking->trackedAt->format('Y-m-d H:i') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                
                @if(!$allCustomerItemsArrived && $effectiveStatus === 'in_transit')
                    <div class="tracking-timeline-item">
                        <div class="tracking-timeline-dot"></div>
                        <div class="tracking-timeline-content">
                            <div class="tracking-timeline-status" style="color: #ffc107;">
                                في الطريق - في انتظار وصول جميع العناصر
                            </div>
                            <div class="tracking-timeline-description">
                                لم تصل جميع العناصر بعد. سيتم تحديث الحالة عند وصول جميع العناصر.
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <p class="tracking-empty-state">
                    <i class="fas fa-info-circle"></i> لا يوجد سجل تتبع متاح حالياً
                </p>
            @endif
        </div>
    </div>
</body>
</html>
