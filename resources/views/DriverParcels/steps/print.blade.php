<div id="step-print" class="print tab">
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-header-left">
                <div class="invoice-logo" id="invoiceOfficeLogo">
                    @if(auth()->user()->office && auth()->user()->office->officeImage)
                        <img src="{{ asset('admin/upload/' . auth()->user()->office->officeImage) }}" 
                             alt="{{ auth()->user()->office->officeName }}">
                    @else
                        <i class="fas fa-box"></i>
                    @endif
                </div>
                <div class="invoice-company-info">
                    <h1 class="invoice-company-name" id="invoiceOfficeName">{{ auth()->user()->office->officeName ?? 'شركة قوافل' }}</h1>
                </div>
            </div>
            <div class="invoice-header-right">
                <div class="invoice-title">
                    <h2>إرسالية سائق</h2>
                </div>
                <div class="invoice-number">
                    <span class="invoice-label">رقم الإرسالية:</span>
                    <span class="invoice-value" id="reviewParcelNumber">-</span>
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
                            <span class="invoice-info-value" id="reviewDriverName">-</span>
                        </div>
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">رقم الهاتف:</span>
                            <span class="invoice-info-value" id="reviewDriverNumber">-</span>
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
                            <span class="invoice-info-value" id="reviewTripName">-</span>
                        </div>
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">الوجهة:</span>
                            <span class="invoice-info-value" id="reviewSendTo">-</span>
                        </div>
                        <div class="invoice-info-item">
                            <span class="invoice-info-label">تاريخ الرحلة:</span>
                            <span class="invoice-info-value" id="reviewTripDate">-</span>
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
                    <span class="invoice-details-value" id="reviewOfficeName">-</span>
                </div>
            </div>
            <div class="invoice-details-right">
                <div class="invoice-details-item">
                    <span class="invoice-details-label">تاريخ الإصدار:</span>
                    <span class="invoice-details-value" id="reviewParcelDate">-</span>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="invoice-items-section">
            <h3 class="invoice-items-title">تفاصيل الإرساليات</h3>
            <div id="reviewParcelsList" class="invoice-items-table">
                <p class="text-muted">لا توجد إرساليات مختارة</p>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="invoice-summary">
            <div class="invoice-summary-left">
                <!-- Empty space for future additions -->
            </div>
            <div class="invoice-summary-right">
                <div class="invoice-summary-row">
                    <span class="invoice-summary-label">التكلفة:</span>
                    <span class="invoice-summary-value" id="reviewCost">-</span>
                </div>
                <div class="invoice-summary-row">
                    <span class="invoice-summary-label">المدفوع:</span>
                    <span class="invoice-summary-value" id="reviewPaid">-</span>
                </div>
                <div class="invoice-summary-row invoice-summary-total">
                    <span class="invoice-summary-label">المتبقي:</span>
                    <span class="invoice-summary-value" id="reviewCostRest">-</span>
                </div>
                <div class="invoice-summary-row">
                    <span class="invoice-summary-label">العملة:</span>
                    <span class="invoice-summary-value" id="reviewCurrency">-</span>
                </div>
            </div>
        </div>

        <!-- Invoice Footer -->
        <div class="invoice-footer">
            <p>شكراً لاستخدامك خدماتنا</p>
            <p class="invoice-footer-note">هذه وثيقة إلكترونية معتمدة</p>
        </div>
    </div>

    <div class="step-actions">
        <button type="button" class="btn btn-info" id="printReviewBtn">
            <i class="fas fa-print"></i>
            <span>طباعة</span>
        </button>
        <a href="{{ route('driver-parcels.index') }}" class="btn btn-primary">
            <i class="fas fa-list"></i>
            <span>العودة إلى القائمة</span>
        </a>
    </div>
</div>

