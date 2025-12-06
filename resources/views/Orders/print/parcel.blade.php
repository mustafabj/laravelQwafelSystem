<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة ارسالية شحن - {{ $parcel->parcelNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Cairo", "Arial", sans-serif;
            direction: rtl;
            background: white;
            color: #000;
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        .company-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #000;
        }
        
        .company-header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }
        
        .company-header .address {
            font-size: 14px;
            margin-bottom: 8px;
            color: #333;
        }
        
        .company-header .contact {
            font-size: 14px;
            color: #333;
        }
        
        .company-header .contact strong {
            font-weight: bold;
        }
        
        .document-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin: 30px 0;
            color: #000;
        }
        
        .parcel-info {
            margin: 20px 0;
            padding: 0;
            border: none;
            font-size: 16px;
            line-height: 1.8;
            text-align: right;
        }
        
        .warning-message {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
            color: #856404;
        }
        
        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            border: 1px solid #000;
        }
        
        .packages-table thead {
            background: #f8f9fa;
        }
        
        .packages-table th,
        .packages-table td {
            border: 1px solid #000;
            padding: 12px;
            text-align: center;
            font-size: 16px;
        }
        
        .packages-table th {
            font-weight: bold;
            background: #e9ecef;
        }
        
        .packages-table td {
            color: #333;
        }
        
        .payment-info {
            margin: 25px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
        
        .terms {
            margin: 15px 0;
            padding: 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        
        .terms ol {
            margin: 5px 0;
            padding-right: 15px;
            font-size: 10px;
            line-height: 1.3;
        }
        
        .terms ol li {
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .signatures {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .signature-box {
            text-align: center;
            min-width: 200px;
        }
        
        .signature-label {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 60px;
            color: #000;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 5px;
            padding-top: 5px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .print-container {
                max-width: 100%;
            }
            
            @page {
                size: A4;
                margin: 15mm;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print();">
    <div class="print-container">
        {{-- Company Header --}}
        <div class="company-header">
            <h1>شركة قوافل السفر لنقل الركاب والبريد</h1>
            <div class="address">عمان-الجاردنز-دخلة مطعم السروات-بجانب ديوان المحاسبة</div>
            <div class="contact">
                <strong>Tel:</strong> 062227100 
                <strong>Mob:</strong> +962798797100 - +962796713271 
                <strong>Iraq:</strong> +9647732248881
            </div>
        </div>

        {{-- Document Title --}}
        <div class="document-title">ارسالية شحن</div>

        {{-- Parcel Information --}}
        <div class="parcel-info">
            رقم الارسالية : {{ $parcel->parcelNumber }} اسم المرسل : {{ $parcel->customer->FName ?? '' }} {{ $parcel->customer->LName ?? '' }} هاتف المرسل : {{ $parcel->customer->phone1 ?? '' }} التاريخ : {{ $parcel->parcelDate }} اسم المرسل اليه : {{ $parcel->recipientName }} العنوان : {{ $parcel->sendTo }} هاتف المرسل اليه : {{ $parcel->recipientNumber }} الى مكتب : {{ $parcel->destinationOffice->officeName ?? '' }}
        </div>

        <div style="margin: 10px 0; font-size: 10px; color: #333; text-align: right; padding: 0; line-height: 1.2;">
            تعتبر هذه الارسالية لاغية بعد 30 يوم من تاريخ اصدارها ولا يحق للمرسل او المرسل اليه باي مطالبة من الشركة
        </div>

        {{-- Packages Table --}}
        <table class="packages-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>معلومات الطرد</th>
                    <th style="width: 100px;">العدد</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parcel->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->detailInfo ?: '07827455393' }}</td>
                    <td>{{ $detail->detailQun }}</td>
                </tr>
                @empty
                <tr>
                    <td>1</td>
                    <td>-</td>
                    <td>1</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Payment Information --}}
        <div style="margin: 20px 0; font-size: 16px; text-align: right;">
            رسوم الشحن : {{ number_format($parcel->cost, 2) }} {{ $parcel->currency }} 
            @if($parcel->paid === 'paid')
                مدفوع
            @elseif($parcel->paid === 'unpaid')
                غير مدفوع
            @else
                تم الدفع لاحقا
            @endif
        </div>

        {{-- Terms --}}
        <div class="terms">
            <ol>
                <li>يحق للشركة فتح الطرد .</li>
                <li>يتحمل المرسل كامل المسؤلية في حال تلف المواد المنقولة خلال الرحلة</li>
                <li>يتحمل المرسل كامل المسؤلية في حال مصادرة المواد والتلافها من قبل اي سلطات مختصة خلال رحلة الشحن .</li>
                <li>سلامة البضاعة مسؤلية السائق .</li>
                <li>في حال فقدان الطرد تقوم الشركة بتعويض المرسل قيمة مبلغ الشحن .</li>
            </ol>
        </div>

        {{-- Signatures --}}
        <div style="margin-top: 30px; text-align: right; font-size: 14px;">
            اسم السائق : توقيع السائق : توقيع المرسل :
        </div>
    </div>
</body>
</html>

