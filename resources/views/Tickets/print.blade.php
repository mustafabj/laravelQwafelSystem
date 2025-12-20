<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة تذكرة سفر - {{ $ticket->tecketNumber }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            color: #1f2937;
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            background: white;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 0;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid #e5e7eb;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .logo i {
            font-size: 28px;
            color: #304e58;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 4px 0;
        }
        
        .company-address {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }
        
        .header-right {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .invoice-title {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .invoice-number {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .label {
            font-weight: 500;
            color: #6b7280;
            font-size: 14px;
        }
        
        .value {
            font-weight: 700;
            color: #304e58;
            font-size: 16px;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 24px;
        }
        
        .info-section {
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .info-content {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            white-space: nowrap;
            flex-shrink: 0;
            min-width: 120px;
        }
        
        .info-value {
            font-weight: 400;
            color: #1f2937;
            font-size: 14px;
            text-align: left;
            flex: 1;
        }
        
        /* Address Section */
        .address-section {
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin: 0 24px 24px 24px;
        }
        
        .address-section .info-grid {
            margin: 0;
        }
        
        .address-section .info-section {
            margin: 0;
        }
        
        .address-section .info-row {
            margin: 0;
        }
        
        /* Summary */
        .summary-section {
            padding: 20px 24px;
            background: #f9fafb;
            border-top: 2px solid #304e58;
            border-bottom: 2px solid #304e58;
            margin: 0 24px 24px 24px;
        }
        
        .summary-section .section-title {
            margin-bottom: 16px;
        }
        
        .summary-content {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            padding-top: 12px;
            margin-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
        
        .summary-label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .summary-value {
            font-weight: 700;
            color: #304e58;
            font-size: 16px;
        }
        
        /* Notes */
        .notes-section {
            padding: 20px 24px;
            background: #f9fafb;
            border-left: 3px solid #304e58;
            margin: 0 24px 24px 24px;
        }
        
        .notes-list {
            margin: 0;
            padding-right: 20px;
            font-size: 13px;
            line-height: 1.8;
            color: #6b7280;
        }
        
        .notes-list li {
            margin-bottom: 8px;
        }
        
        /* Print Styles */
        @media print {
            body {
                padding: 0;
            }
            
            .invoice-wrapper {
                max-width: 100%;
            }
            
            .invoice-header {
                page-break-after: avoid;
            }
            
            .summary-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body onload="window.print();">
    <div class="invoice-wrapper">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="logo">
                    @if($ticket->office && $ticket->office->officeImage)
                        <img src="{{ asset('admin/upload/' . $ticket->office->officeImage) }}" 
                             alt="{{ $ticket->office->officeName }}" />
                    @else
                        <i class="fas fa-bus"></i>
                    @endif
                </div>
                <div class="company-info">
                    <h1 class="company-name">{{ $ticket->office->officeName ?? 'شركة قوافل' }}</h1>
                    <p class="company-address">{{ $ticket->office->officeAddress ?? '' }}</p>
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">تذكرة سفر</div>
                <div class="invoice-number">
                    <span class="label">رقم التذكرة:</span>
                    <span class="value">{{ $ticket->tecketNumber }}</span>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-section">
                <h3 class="section-title">معلومات المسافر</h3>
                <div class="info-content">
                    <div class="info-row">
                        <span class="info-label">اسم المسافر:</span>
                        <span class="info-value">{{ $ticket->customer->FName }} {{ $ticket->customer->LName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">هاتف المسافر:</span>
                        <span class="info-value">{{ $ticket->customer->custNumber }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">رقم جواز السفر:</span>
                        <span class="info-value">{{ $ticket->customer->customerPassport ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">تاريخ التذكرة:</span>
                        <span class="info-value">{{ $ticket->ticketDate }}</span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <h3 class="section-title">معلومات السفر</h3>
                <div class="info-content">
                    <div class="info-row">
                        <span class="info-label">تاريخ السفر:</span>
                        <span class="info-value">{{ $ticket->travelDate }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">وقت السفر:</span>
                        <span class="info-value">{{ $ticket->formatted_time }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">رقم المقعد:</span>
                        <span class="info-value">{{ $ticket->Seat }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">جهة السفر:</span>
                        <span class="info-value">{{ $ticket->destination }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Section -->
        @if($ticket->address)
            <div class="address-section">
                <h3 class="section-title">عنوان المسافر</h3>
                <div class="info-grid">
                    <div class="info-section">
                        <div class="info-content">
                            <div class="info-row">
                                <span class="info-label">المدينة:</span>
                                <span class="info-value">{{ $ticket->address->city }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">المنطقة:</span>
                                <span class="info-value">{{ $ticket->address->area }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <div class="info-content">
                            <div class="info-row">
                                <span class="info-label">اسم الشارع:</span>
                                <span class="info-value">{{ $ticket->address->street }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">رقم المبنى:</span>
                                <span class="info-value">{{ $ticket->address->buildingNumber }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @if($ticket->address->info)
                    <div class="info-content" style="margin-top: 16px;">
                        <div class="info-row">
                            <span class="info-label">معلومات إضافية:</span>
                            <span class="info-value">{{ $ticket->address->info }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Summary -->
        <div class="summary-section">
            <h3 class="section-title">ملخص الدفع</h3>
            <div class="summary-content">
                <div class="summary-row">
                    <span class="summary-label">سعر التذكرة:</span>
                    <span class="summary-value">{{ $ticket->cost }} {{ $ticket->currency_name }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">المبلغ المدفوع:</span>
                    <span class="summary-value">{{ $ticket->costRest }} {{ $ticket->currency_name }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">باقي المبلغ:</span>
                    <span class="summary-value">{{ $ticket->unpaid_amount }} {{ $ticket->currency_name }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">حالة الدفع:</span>
                    <span class="summary-value">{{ $ticket->paid_text }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="notes-section">
            <ol class="notes-list">
                <li>في حال الغاء موعد السفر يفقد قيمة التذكرة كاملة.</li>
                <li>يحق لكل راكب شنطة 30 كيلو فقط.</li>
            </ol>
        </div>
    </div>
</body>
</html>

