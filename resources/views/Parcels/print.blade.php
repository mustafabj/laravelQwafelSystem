<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة الطرد</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>
<body onload="window.print();">
    <div class="invoice">
        <h1>تفاصيل الطرد رقم {{ $parcel->parcelId }}</h1>
    </div>
</body>
</html>
