<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة تذكرة سفر - {{ $ticket->tecketNumber }}</title>
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
            text-decoration: underline;
        }
        
        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            border: 2px solid #000;
        }
        
        .ticket-table td {
            border: 1px solid #000;
            padding: 15px;
            font-size: 18px;
        }
        
        .ticket-table td:first-child {
            background: #f8f9fa;
            font-weight: bold;
            width: 40%;
            text-align: right;
        }
        
        .ticket-table td:last-child {
            text-align: right;
            color: #333;
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
            <h1>شركة قوافل السفر لنقل الركاب</h1>
            <h2 style="font-size: 20px; margin: 10px 0;">البريد</h2>
            <div class="address">عمان-الجاردرز-دخلة مطعم السروات-بجانب ديوان المحاسبة</div>
            <div class="contact">
                <strong>Tel:</strong> 062227100 
                <strong>Mob:</strong> +962798797100 - +962796713271 
                <strong>Iraq:</strong> +9647732248881
            </div>
        </div>

        {{-- Document Title --}}
        <div class="document-title">تذكرة سفر</div>

        {{-- Ticket Information Table --}}
        <table class="ticket-table">
            <tr>
                <td>رقم التذكرة :</td>
                <td>{{ $ticket->tecketNumber }}</td>
            </tr>
            <tr>
                <td>اسم المسافر :</td>
                <td>{{ $ticket->customer->FName ?? '' }} {{ $ticket->customer->LName ?? '' }}</td>
            </tr>
            <tr>
                <td>هاتف المسافر :</td>
                <td>{{ $ticket->customer->phone1 ?? '' }}</td>
            </tr>
            <tr>
                <td>تاريخ السفر :</td>
                <td>{{ $ticket->travelDate ?: '-' }}</td>
            </tr>
            <tr>
                <td>رقم المقعد :</td>
                <td>{{ $ticket->Seat ?: '-' }}</td>
            </tr>
            <tr>
                <td>سعر التذكرة :</td>
                <td>{{ number_format($ticket->cost, 2) }} {{ $ticket->currency }}</td>
            </tr>
            <tr>
                <td>باقي المبلغ غير الواصل :</td>
                <td>{{ number_format($ticket->costRest ?? 0, 2) }} {{ $ticket->currency }}</td>
            </tr>
        </table>

        {{-- Terms --}}
        <div class="terms">
            <ol>
                <li>في حال الغاء موعد السفر يفقد قيمة التذكرة كاملة .</li>
                <li>يحق لكل راكب شنطة 30 كيلو فقط</li>
            </ol>
        </div>
    </div>
</body>
</html>

