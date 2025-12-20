<div class="simple-modal-content">
    <!-- Header -->
    <div class="simple-modal-header">
        <h2>تذكرة سفر - رقم {{ $ticket->tecketNumber }}</h2>
        <div class="simple-modal-actions noPrint">
            <a href="{{ route('ticket.print', $ticket->ticketId) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> طباعة
            </a>
            @if($ticket->accept === 'no' && $ticket->userId != auth()->id())
                <button class="btn btn-success btn-sm" onclick="acceptticket({{ $ticket->ticketId }})">
                    <i class="fas fa-check"></i> قبول
                </button>
            @endif
        </div>
    </div>

    <!-- Content -->
    <div class="simple-modal-body">
        <!-- Passenger Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">معلومات المسافر</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">اسم المسافر:</span>
                    <span class="simple-value">{{ $ticket->customer->FName }} {{ $ticket->customer->LName }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">هاتف المسافر:</span>
                    <span class="simple-value">{{ $ticket->customer->custNumber }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">رقم جواز السفر:</span>
                    <span class="simple-value">{{ $ticket->customer->customerPassport ?? '-' }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">تاريخ التذكرة:</span>
                    <span class="simple-value">{{ $ticket->ticketDate }}</span>
                </div>
            </div>
        </div>

        <!-- Travel Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">معلومات السفر</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">تاريخ السفر:</span>
                    <span class="simple-value">{{ $ticket->travelDate }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">وقت السفر:</span>
                    <span class="simple-value">{{ $ticket->formatted_time }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">رقم المقعد:</span>
                    <span class="simple-value">{{ $ticket->Seat }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">جهة السفر:</span>
                    <span class="simple-value">{{ $ticket->destination }}</span>
                </div>
            </div>
        </div>

        <!-- Address Section -->
        @if($ticket->address)
            <div class="simple-section noPrint">
                <h3 class="simple-section-title">عنوان المسافر</h3>
                <div class="simple-data-grid">
                    <div class="simple-data-item">
                        <span class="simple-label">المدينة:</span>
                        <span class="simple-value">{{ $ticket->address->city }}</span>
                    </div>
                    <div class="simple-data-item">
                        <span class="simple-label">المنطقة:</span>
                        <span class="simple-value">{{ $ticket->address->area }}</span>
                    </div>
                    <div class="simple-data-item">
                        <span class="simple-label">اسم الشارع:</span>
                        <span class="simple-value">{{ $ticket->address->street }}</span>
                    </div>
                    <div class="simple-data-item">
                        <span class="simple-label">رقم المبنى:</span>
                        <span class="simple-value">{{ $ticket->address->buildingNumber }}</span>
                    </div>
                    @if($ticket->address->info)
                        <div class="simple-data-item full-width">
                            <span class="simple-label">معلومات إضافية:</span>
                            <span class="simple-value">{{ $ticket->address->info }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Payment Information -->
        <div class="simple-section">
            <h3 class="simple-section-title">المعلومات المالية</h3>
            <div class="simple-data-grid">
                <div class="simple-data-item">
                    <span class="simple-label">سعر التذكرة:</span>
                    <span class="simple-value">{{ $ticket->cost }} {{ $ticket->currency_name }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">المبلغ المدفوع:</span>
                    <span class="simple-value">{{ $ticket->costRest }} {{ $ticket->currency_name }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">باقي المبلغ:</span>
                    <span class="simple-value">{{ $ticket->unpaid_amount }} {{ $ticket->currency_name }}</span>
                </div>
                <div class="simple-data-item">
                    <span class="simple-label">حالة الدفع:</span>
                    <span class="simple-value">{{ $ticket->paid_text }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
