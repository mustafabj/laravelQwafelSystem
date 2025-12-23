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
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .back-btn, .logout-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn {
            background: #dc3545;
        }
        .parcel-details {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 500;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-in_transit { background: #17a2b8; color: white; }
        .status-arrived { background: #28a745; color: white; }
        .status-delivered { background: #6c757d; color: white; }
        .tracking-timeline {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .timeline-item {
            display: flex;
            padding: 15px 0;
            border-right: 2px solid #eee;
            position: relative;
            padding-right: 30px;
        }
        .timeline-item:last-child {
            border-right: none;
        }
        .timeline-item.active {
            border-right-color: #28a745;
        }
        .timeline-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #eee;
            position: absolute;
            right: -9px;
            top: 20px;
        }
        .timeline-item.active .timeline-dot {
            background: #28a745;
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.2);
        }
        .timeline-content {
            flex: 1;
        }
        .timeline-status {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .timeline-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .timeline-date {
            color: #999;
            font-size: 12px;
        }
        .timeline-location {
            color: #007bff;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-box"></i> تتبع الإرسالية #{{ $driverParcel->parcelNumber }}</h1>
            </div>
            <div>
                <a href="{{ route('customer-portal.dashboard') }}" class="back-btn">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
                <form method="POST" action="{{ route('customer-portal.logout') }}" style="display: inline-block; margin-right: 10px;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>

        <div class="parcel-details">
            <h2 style="margin-top: 0;">معلومات الإرسالية</h2>
            
            <div class="detail-row">
                <span class="detail-label">رقم الإرسالية:</span>
                <span class="detail-value"><strong>#{{ $driverParcel->parcelNumber }}</strong></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">حالة إرساليتك:</span>
                <span class="detail-value">
                    <span class="status-badge status-{{ $effectiveStatus }}">
                        @if($effectiveStatus === 'pending') قيد الانتظار
                        @elseif($effectiveStatus === 'in_transit') في الطريق
                        @elseif($effectiveStatus === 'arrived') وصلت
                        @elseif($effectiveStatus === 'delivered') تم التسليم
                        @endif
                    </span>
                </span>
            </div>
            
            @if($driverParcel->trip)
                <div class="detail-row">
                    <span class="detail-label">الرحلة:</span>
                    <span class="detail-value">{{ $driverParcel->trip->tripName }}</span>
                </div>
            @endif
            
            @if($driverParcel->office)
                <div class="detail-row">
                    <span class="detail-label">المكتب:</span>
                    <span class="detail-value">{{ $driverParcel->office->officeName }}</span>
                </div>
            @endif
            
            @if($driverParcel->tripDate)
                <div class="detail-row">
                    <span class="detail-label">تاريخ الرحلة:</span>
                    <span class="detail-value">{{ $driverParcel->tripDate->format('Y-m-d') }}</span>
                </div>
            @endif
            
            @if($driverParcel->parcelDate)
                <div class="detail-row">
                    <span class="detail-label">تاريخ الإنشاء:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($driverParcel->parcelDate)->format('Y-m-d H:i') }}</span>
                </div>
            @endif
            
            @if($driverParcel->arrivedAt)
                <div class="detail-row">
                    <span class="detail-label">تاريخ الوصول:</span>
                    <span class="detail-value">{{ $driverParcel->arrivedAt->format('Y-m-d H:i') }}</span>
                </div>
            @endif
            
            @if($driverParcel->delayReason)
                <div class="detail-row">
                    <span class="detail-label">سبب التأخير:</span>
                    <span class="detail-value" style="color: #dc3545;">{{ $driverParcel->delayReason }}</span>
                </div>
            @endif
        </div>

        <div class="tracking-timeline">
            <h2 style="margin-top: 0;">سجل التتبع</h2>
            
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
                        <div class="timeline-item {{ $index === 0 && $lastStatus === $tracking->status ? 'active' : '' }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-status">
                                    @if($tracking->status === 'pending') قيد الانتظار
                                    @elseif($tracking->status === 'in_transit') في الطريق
                                    @elseif($tracking->status === 'arrived') وصلت جميع العناصر
                                    @elseif($tracking->status === 'delivered') تم التسليم
                                    @endif
                                </div>
                                @if($tracking->description)
                                    <div class="timeline-description">{{ $tracking->description }}</div>
                                @endif
                                @if($tracking->location)
                                    <div class="timeline-location">
                                        <i class="fas fa-map-marker-alt"></i> {{ $tracking->location }}
                                    </div>
                                @endif
                                @if($tracking->location)
                                    <div class="timeline-location">
                                        <i class="fas fa-map-marker-alt"></i> {{ $tracking->location }}
                                    </div>
                                @endif
                                <div class="timeline-date">
                                    <i class="fas fa-clock"></i> {{ $tracking->trackedAt->format('Y-m-d H:i') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                
                @if(!$allCustomerItemsArrived && $effectiveStatus === 'in_transit')
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-status" style="color: #ffc107;">
                                في الطريق - في انتظار وصول جميع العناصر
                            </div>
                            <div class="timeline-description">
                                لم تصل جميع العناصر بعد. سيتم تحديث الحالة عند وصول جميع العناصر.
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <p style="text-align: center; color: #666; padding: 20px;">
                    <i class="fas fa-info-circle"></i> لا يوجد سجل تتبع متاح حالياً
                </p>
            @endif
        </div>
    </div>
</body>
</html>

