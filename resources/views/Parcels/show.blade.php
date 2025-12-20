<div class="simple-modal-content">
    <!-- Header -->
    <div class="simple-modal-header">
        <h2>إرسالية شحن - رقم {{ $parcel->parcelNumber }}</h2>
        <div class="simple-modal-actions noPrint">
            <a href="{{ route('parcel.print', $parcel->parcelId) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> طباعة
            </a>
            @if($parcel->accept === 'no' && $parcel->userId !== $user->id)
                <button class="btn btn-success btn-sm" onclick="acceptparcel({{ $parcel->parcelId }})" type="button">
                    <i class="fas fa-check"></i> قبول
                </button>
            @endif
        </div>
    </div>

    <!-- Content -->
    <div class="simple-modal-body">
        <!-- Sender Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">معلومات المرسل</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">اسم المرسل:</span>
                    <span class="simple-value">{{ $parcel->customer->FName }} {{ $parcel->customer->LName }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">هاتف المرسل:</span>
                    <span class="simple-value">{{ $parcel->custNumber }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">التاريخ:</span>
                    <span class="simple-value">{{ $parcel->parcelDate }}</span>
                </div>
            </div>
        </div>

        <!-- Recipient Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">معلومات المرسل إليه</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">اسم المرسل إليه:</span>
                    <span class="simple-value">{{ $parcel->recipientName }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">هاتف المرسل إليه:</span>
                    <span class="simple-value">{{ $parcel->recipientNumber }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">العنوان:</span>
                    <span class="simple-value">{{ $parcel->sendTo }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">إلى مكتب:</span>
                    <span class="simple-value">{{ $parcel->destinationOffice->officeName ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Notice -->
        @if($parcel->paid === 'unpaid' && $parcel->paidInMainOffice)
            <div class="simple-alert noPrint">
                <i class="fas fa-info-circle"></i>
                <span>الدفع في مكتب المرسل: {{ $parcel->mainOffice?->officeName }}</span>
            </div>
        @endif

        <!-- Parcel Details -->
        <div class="simple-section">
            <h3 class="simple-section-title">تفاصيل الطرود</h3>
            <div class="simple-table-container">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>معلومات الطرد</th>
                            <th>العدد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parcel->details as $detail)
                            <tr>
                                <td>{{ $detail->detailInfo }}</td>
                                <td>{{ $detail->detailQun }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">المعلومات المالية</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">رسوم الشحن:</span>
                    <span class="simple-value" id="costparcel">{{ intval($parcel->cost) }} {{ $parcel->currency }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">حالة الدفع:</span>
                    <span class="simple-value" id="costreceiptparcel">{{ $parcel->paid === 'paid' ? 'مدفوع' : 'غير مدفوع' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
