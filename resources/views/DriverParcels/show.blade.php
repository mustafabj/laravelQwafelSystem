<div class="simple-modal-content">
    <!-- Header -->
    <div class="simple-modal-header">
        <h2>إرسالية سائق - رقم {{ $driverParcel->parcelNumber }}</h2>
        <div class="simple-modal-actions noPrint">
            <a href="{{ route('driver-parcels.print', $driverParcel->parcelId) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> طباعة
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="simple-modal-body">
        <!-- Driver Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">معلومات السائق</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">اسم السائق:</span>
                    <span class="simple-value">{{ $driverParcel->driverName }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">رقم الهاتف:</span>
                    <span class="simple-value">{{ $driverParcel->driverNumber }}</span>
                </div>
            </div>
        </div>

        <!-- Trip Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">معلومات الرحلة</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">اسم الرحلة:</span>
                    <span class="simple-value">{{ $driverParcel->trip->tripName ?? '-' }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">الوجهة:</span>
                    <span class="simple-value">{{ $driverParcel->sendTo }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">تاريخ الرحلة:</span>
                    <span class="simple-value">{{ $driverParcel->tripDate ? \Carbon\Carbon::parse($driverParcel->tripDate)->format('Y-m-d') : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Office and Date -->
        <div class="simple-section">
            <h3 class="simple-section-title">معلومات إضافية</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">المكتب:</span>
                    <span class="simple-value">{{ $driverParcel->office->officeName ?? '-' }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">تاريخ الإصدار:</span>
                    <span class="simple-value">{{ $driverParcel->parcelDate }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">الحالة:</span>
                    <span class="simple-value">
                        @php
                            $statusLabels = [
                                'pending' => 'قيد الانتظار',
                                'in_transit' => 'قيد النقل',
                                'arrived' => 'وصلت',
                                'delivered' => 'تم التسليم',
                            ];
                            $statusLabel = $statusLabels[$driverParcel->status] ?? $driverParcel->status;
                        @endphp
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Parcel Details -->
        <div class="simple-section">
            <h3 class="simple-section-title">تفاصيل الإرساليات</h3>
            <div class="simple-table-container">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>معلومات الطرد</th>
                            <th>العدد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driverParcel->details as $detail)
                            <tr>
                                <td>{{ $detail->detailInfo ?? ($detail->parcelDetail->detailInfo ?? '-') }}</td>
                                <td>{{ $detail->detailQun }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">لا توجد إرساليات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">المعلومات المالية</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">التكلفة:</span>
                    <span class="simple-value">{{ intval($driverParcel->cost) }} {{ $driverParcel->currency }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">المدفوع:</span>
                    <span class="simple-value">{{ intval($driverParcel->paid) }} {{ $driverParcel->currency }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">المتبقي:</span>
                    <span class="simple-value">{{ intval($driverParcel->costRest) }} {{ $driverParcel->currency }}</span>
                </div>
            </div>
        </div>

        <!-- Tracking History -->
        @if(isset($trackingHistory) && $trackingHistory->count() > 0)
        <div class="simple-section">
            <h3 class="simple-section-title">سجل التتبع</h3>
            <div class="simple-table-container">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>التاريخ والوقت</th>
                            <th>الحالة</th>
                            <th>الموقع</th>
                            <th>الوصف</th>
                            <th>العميل</th>
                            <th>تم التتبع بواسطة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trackingHistory as $tracking)
                            <tr>
                                <td>{{ $tracking->trackedAt->format('Y-m-d H:i') }}</td>
                                <td>
                                    @php
                                        $statusLabels = [
                                            'pending' => 'قيد الانتظار',
                                            'in_transit' => 'في الطريق',
                                            'arrived' => 'وصلت',
                                            'delivered' => 'تم التسليم',
                                        ];
                                        $statusLabel = $statusLabels[$tracking->status] ?? $tracking->status;
                                    @endphp
                                    <span class="badge badge-{{ $tracking->status === 'arrived' ? 'success' : ($tracking->status === 'in_transit' ? 'info' : 'warning') }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>{{ $tracking->location ?? '-' }}</td>
                                <td>{{ $tracking->description ?? '-' }}</td>
                                <td>
                                    @if($tracking->driverParcelDetail && $tracking->driverParcelDetail->parcelDetail && $tracking->driverParcelDetail->parcelDetail->parcel && $tracking->driverParcelDetail->parcelDetail->parcel->customer)
                                        {{ $tracking->driverParcelDetail->parcelDetail->parcel->customer->FName }} {{ $tracking->driverParcelDetail->parcelDetail->parcel->customer->LName }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $tracking->trackedBy ?? 'system' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Items Status -->
        <div class="simple-section">
            <h3 class="simple-section-title">حالة العناصر</h3>
            <div class="simple-table-container">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>معلومات الطرد</th>
                            <th>العدد</th>
                            <th>خرج من المكتب</th>
                            <th>وصل</th>
                            <th>تاريخ الوصول</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driverParcel->details as $detail)
                            <tr>
                                <td>{{ $detail->detailInfo ?? ($detail->parcelDetail->detailInfo ?? '-') }}</td>
                                <td>{{ $detail->quantityTaken ?? $detail->detailQun }}</td>
                                <td>
                                    @if($detail->leftOfficeAt)
                                        <span class="badge badge-success">{{ $detail->leftOfficeAt->format('Y-m-d H:i') }}</span>
                                    @else
                                        <span class="badge badge-warning">لم يخرج</span>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->isArrived)
                                        <span class="badge badge-success">نعم</span>
                                    @else
                                        <span class="badge badge-warning">لا</span>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->arrivedAt)
                                        {{ $detail->arrivedAt->format('Y-m-d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">لا توجد عناصر</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
