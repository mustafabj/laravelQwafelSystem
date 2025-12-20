<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة إرسالية سائق - {{ $driverParcel->parcelNumber }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Cairo", sans-serif;
            direction: rtl;
            background: white;
            color: #000;
            padding: 15px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
        }
        
        /* Invoice Header - Compact */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 2px solid #304e58;
        }
        
        .invoice-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .invoice-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #304e58 0%, #3a5f6b 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .invoice-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .invoice-company-name {
            font-size: 20px;
            font-weight: 700;
            color: #304e58;
            margin: 0;
        }
        
        .invoice-header-right {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .invoice-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        
        .invoice-number {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .invoice-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }
        
        .invoice-value {
            font-size: 18px;
            font-weight: 700;
            color: #304e58;
        }
        
        /* Invoice Info Section - Compact */
        .invoice-info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .invoice-info-title {
            font-size: 16px;
            font-weight: 700;
            color: #304e58;
            margin: 0 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #304e58;
        }
        
        .invoice-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        
        .invoice-info-label {
            font-weight: 600;
            color: #6b7280;
            margin-left: 15px;
            font-size: 14px;
        }
        
        .invoice-info-value {
            font-weight: 600;
            color: #111827;
            font-size: 15px;
        }
        
        /* Invoice Details Section - Compact */
        .invoice-details-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            padding: 12px 15px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        
        .invoice-details-item {
            display: flex;
            justify-content: space-between;
        }
        
        .invoice-details-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }
        
        .invoice-details-value {
            font-weight: 600;
            color: #111827;
            font-size: 15px;
        }
        
        /* Invoice Items Section - Compact */
        .invoice-items-section {
            margin-bottom: 20px;
        }
        
        .invoice-items-title {
            font-size: 18px;
            font-weight: 700;
            color: #304e58;
            margin: 0 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #304e58;
        }
        
        .invoice-items-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .invoice-items-table thead {
            background: linear-gradient(135deg, #304e58 0%, #3a5f6b 100%);
            color: white;
        }
        
        .invoice-items-table th {
            padding: 10px 12px;
            text-align: right;
            font-weight: 700;
            font-size: 13px;
            border: none;
        }
        
        .invoice-items-table td {
            padding: 8px 12px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            color: #374151;
        }
        
        .invoice-items-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        .invoice-items-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Invoice Summary - Compact */
        .invoice-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .invoice-summary-right {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        
        .invoice-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .invoice-summary-row:last-child {
            border-bottom: none;
        }
        
        .invoice-summary-total {
            border-top: 2px solid #304e58;
            border-bottom: 2px solid #304e58;
            margin-top: 8px;
            padding-top: 10px;
            padding-bottom: 10px;
        }
        
        .invoice-summary-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 15px;
            flex-shrink: 0;
            margin-left: 15px;
        }
        
        .invoice-summary-value {
            font-weight: 700;
            color: #111827;
            font-size: 16px;
            text-align: left;
        }
        
        .invoice-summary-total .invoice-summary-label {
            font-size: 18px;
            color: #304e58;
        }
        
        .invoice-summary-total .invoice-summary-value {
            font-size: 20px;
            color: #304e58;
        }
        
        /* Invoice Footer - Compact */
        .invoice-footer {
            text-align: center;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        
        .invoice-footer p {
            margin: 5px 0;
        }
        
        .invoice-footer-note {
            font-size: 11px;
            color: #9ca3af;
            font-style: italic;
        }
        
        @media print {
            body { 
                margin: 0; 
                padding: 15px; 
            }
            .no-print { 
                display: none; 
            }
            .invoice-container { 
                box-shadow: none; 
                max-width: 100%;
            }
            .invoice-header { 
                page-break-after: avoid; 
            }
            .invoice-items-section { 
                page-break-inside: avoid; 
            }
            .invoice-items-table tbody { 
                page-break-inside: auto; 
            }
            .invoice-items-table tbody tr { 
                page-break-after: auto; 
                page-break-inside: avoid; 
            }
            .invoice-summary { 
                page-break-inside: avoid; 
            }
            .invoice-footer { 
                page-break-inside: avoid; 
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-header-left">
                <div class="invoice-logo">
                    @if($office && $office->officeImage)
                        <img src="{{ asset('image/' . $office->officeImage) }}" 
                             alt="{{ $office->officeName }}">
                    @else
                        <i class="fas fa-box"></i>
                    @endif
                </div>
                <div class="invoice-company-info">
                    <h1 class="invoice-company-name">{{ $office->officeName ?? 'شركة قوافل' }}</h1>
                </div>
            </div>
            <div class="invoice-header-right">
                <div class="invoice-title">
                    <h2>إرسالية سائق</h2>
                </div>
                <div class="invoice-number">
                    <span class="invoice-label">رقم الإرسالية:</span>
                    <span class="invoice-value">{{ $driverParcel->parcelNumber }}</span>
                </div>
            </div>
        </div>

        <!-- Invoice Info Section -->
        <div class="invoice-info-section">
            <div class="invoice-info-left">
                <div class="invoice-info-block">
                    <h3 class="invoice-info-title">معلومات السائق</h3>
                    <div class="invoice-info-content">
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">الاسم:</span>
                            <span class="invoice-info-value">{{ $driverParcel->driverName }}</span>
                        </div>
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">رقم الهاتف:</span>
                            <span class="invoice-info-value">{{ $driverParcel->driverNumber }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="invoice-info-right">
                <div class="invoice-info-block">
                    <h3 class="invoice-info-title">معلومات الرحلة</h3>
                    <div class="invoice-info-content">
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">اسم الرحلة:</span>
                            <span class="invoice-info-value">{{ $driverParcel->trip->tripName ?? '-' }}</span>
                        </div>
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">الوجهة:</span>
                            <span class="invoice-info-value">{{ $driverParcel->sendTo }}</span>
                        </div>
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">تاريخ الرحلة:</span>
                            <span class="invoice-info-value">
                                @if($driverParcel->tripDate)
                                    {{ \Carbon\Carbon::parse($driverParcel->tripDate)->locale('ar')->translatedFormat('d F Y') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Details Section -->
        <div class="invoice-details-section">
            <div class="invoice-details-left">
                <div class="invoice-details-item">
                    <span class="invoice-details-label">المكتب:</span>
                    <span class="invoice-details-value">{{ $driverParcel->office->officeName ?? '-' }}</span>
                </div>
            </div>
            <div class="invoice-details-right">
                <div class="invoice-details-item">
                    <span class="invoice-details-label">تاريخ الإصدار:</span>
                    <span class="invoice-details-value">
                        @if($driverParcel->parcelDate)
                            {{ \Carbon\Carbon::parse($driverParcel->parcelDate)->locale('ar')->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="invoice-items-section">
            <h3 class="invoice-items-title">تفاصيل الإرساليات</h3>
            <table class="invoice-items-table">
                <thead>
                    <tr>
                        <th>رقم الإرسالية</th>
                        <th>اسم العميل</th>
                        <th>الوصف</th>
                        <th>الكمية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($driverParcel->details as $detail)
                        <tr>
                            <td>{{ $detail->parcelDetail->parcel->parcelNumber ?? '-' }}</td>
                            <td>
                                @if($detail->parcelDetail->parcel->customer)
                                    {{ $detail->parcelDetail->parcel->customer->FName }} {{ $detail->parcelDetail->parcel->customer->LName }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $detail->detailInfo }}</td>
                            <td>{{ $detail->quantityTaken }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: #6b7280;">
                                لا توجد إرساليات
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Invoice Summary -->
        <div class="invoice-summary">
            <div class="invoice-summary-left">
                <!-- Empty space for future additions -->
            </div>
            <div class="invoice-summary-right">
                <div class="invoice-summary-row">
                    <span class="invoice-summary-label">التكلفة:</span>
                    <span class="invoice-summary-value">
                        @if($driverParcel->cost && $driverParcel->cost > 0)
                            {{ number_format($driverParcel->cost, 2) }} {{ $driverParcel->currency === 'IQD' ? 'دينار عراقي' : ($driverParcel->currency === 'USD' ? 'دولار' : 'يورو') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="invoice-summary-row">
                    <span class="invoice-summary-label">المدفوع:</span>
                    <span class="invoice-summary-value">
                        @if($driverParcel->paid && $driverParcel->paid > 0)
                            {{ number_format($driverParcel->paid, 2) }} {{ $driverParcel->currency === 'IQD' ? 'دينار عراقي' : ($driverParcel->currency === 'USD' ? 'دولار' : 'يورو') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="invoice-summary-row invoice-summary-total">
                    <span class="invoice-summary-label">المتبقي:</span>
                    <span class="invoice-summary-value">
                        @if($driverParcel->costRest && $driverParcel->costRest > 0)
                            {{ number_format($driverParcel->costRest, 2) }} {{ $driverParcel->currency === 'IQD' ? 'دينار عراقي' : ($driverParcel->currency === 'USD' ? 'دولار' : 'يورو') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="invoice-summary-row">
                    <span class="invoice-summary-label">العملة:</span>
                    <span class="invoice-summary-value">
                        {{ $driverParcel->currency === 'IQD' ? 'دينار عراقي' : ($driverParcel->currency === 'USD' ? 'دولار' : 'يورو') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Invoice Footer -->
        <div class="invoice-footer">
            <p>شكراً لاستخدامك خدماتنا</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

