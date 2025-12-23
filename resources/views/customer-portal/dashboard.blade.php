<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة متابعة الإرساليات - {{ config('app.name') }}</title>
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
            max-width: 1200px;
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
        .header h1 {
            margin: 0;
            color: #333;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .parcels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .parcel-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .parcel-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .parcel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .parcel-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-in_transit { background: #17a2b8; color: white; }
        .status-arrived { background: #28a745; color: white; }
        .status-delivered { background: #6c757d; color: white; }
        .parcel-info {
            margin-bottom: 15px;
        }
        .parcel-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #666;
        }
        .parcel-info-label {
            font-weight: 500;
        }
        .track-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            width: 100%;
        }
        .track-btn:hover {
            background: #0056b3;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-box"></i> متابعة الإرساليات</h1>
                <p style="margin: 5px 0 0 0; color: #666;">مرحباً {{ $customer->fullName }}</p>
            </div>
            <form method="POST" action="{{ route('customer-portal.logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </button>
            </form>
        </div>

        @if($parcels->count() > 0)
            <div class="parcels-grid">
                @foreach($parcels as $parcel)
                    <div class="parcel-card">
                        <div class="parcel-header">
                            <div class="parcel-number">
                                <i class="fas fa-box"></i> #{{ $parcel->parcelNumber }}
                            </div>
                            <span class="status-badge status-{{ $parcel->effectiveStatus ?? $parcel->status }}">
                                @if(($parcel->effectiveStatus ?? $parcel->status) === 'pending') قيد الانتظار
                                @elseif(($parcel->effectiveStatus ?? $parcel->status) === 'in_transit') في الطريق
                                @elseif(($parcel->effectiveStatus ?? $parcel->status) === 'arrived') وصلت جميع العناصر
                                @elseif(($parcel->effectiveStatus ?? $parcel->status) === 'delivered') تم التسليم
                                @endif
                            </span>
                        </div>
                        
                        
                        <div class="parcel-info">
                            @if($parcel->trip)
                                <div class="parcel-info-item">
                                    <span class="parcel-info-label"><i class="fas fa-route"></i> الرحلة:</span>
                                    <span>{{ $parcel->trip->tripName }}</span>
                                </div>
                            @endif
                            
                            @if($parcel->office)
                                <div class="parcel-info-item">
                                    <span class="parcel-info-label"><i class="fas fa-building"></i> المكتب:</span>
                                    <span>{{ $parcel->office->officeName }}</span>
                                </div>
                            @endif
                            
                            @if($parcel->tripDate)
                                <div class="parcel-info-item">
                                    <span class="parcel-info-label"><i class="fas fa-calendar"></i> تاريخ الرحلة:</span>
                                    <span>{{ $parcel->tripDate->format('Y-m-d') }}</span>
                                </div>
                            @endif
                            
                            @if($parcel->parcelDate)
                                <div class="parcel-info-item">
                                    <span class="parcel-info-label"><i class="fas fa-clock"></i> تاريخ الإنشاء:</span>
                                    <span>{{ \Carbon\Carbon::parse($parcel->parcelDate)->format('Y-m-d') }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('customer-portal.track', $parcel->parcelId) }}" class="track-btn">
                            <i class="fas fa-search"></i> تتبع الإرسالية
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>لا توجد إرساليات</h3>
                <p>لم يتم العثور على أي إرساليات مرتبطة برقم هاتفك</p>
            </div>
        @endif
    </div>
</body>
</html>

