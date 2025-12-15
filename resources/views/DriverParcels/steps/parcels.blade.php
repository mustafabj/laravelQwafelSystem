<div id="step-parcels" class="parcels tab">

    {{-- ================= HEADER ================= --}}
    <div class="step-header">
        <h2 class="step-title">
            <span class="step-icon">📦</span>
            اختيار الإرساليات
        </h2>
        <p class="step-description">
            اختر الإرساليات المراد إضافتها لهذه الإرسالية
        </p>
    </div>

    {{-- ================= SUMMARY ================= --}}
    <div class="parcel-summary-card" id="parcelSummaryCard" style="display:none">
        <div class="summary-header">
            <h3 class="summary-title">
                <i class="fas fa-clipboard-check"></i>
                ملخص البيانات المختارة
            </h3>
        </div>

        <div class="summary-content">
            <div class="summary-section">
                <h4><i class="fas fa-user-tie"></i> معلومات السائق</h4>
                <div class="summary-item">
                    <span>اسم السائق:</span>
                    <strong id="summaryDriverName">-</strong>
                </div>
                <div class="summary-item">
                    <span>رقم الهاتف:</span>
                    <strong id="summaryDriverPhone">-</strong>
                </div>
            </div>

            <div class="summary-section">
                <h4><i class="fas fa-route"></i> معلومات الرحلة</h4>
                <div class="summary-item">
                    <span>اسم الرحلة:</span>
                    <strong id="summaryTripName">-</strong>
                </div>
                <div class="summary-item">
                    <span>الوجهة:</span>
                    <strong id="summaryDestination">-</strong>
                </div>
                <div class="summary-item">
                    <span>تاريخ الرحلة:</span>
                    <strong id="summaryTripDate">-</strong>
                </div>
            </div>

            <div class="summary-section">
                <h4><i class="fas fa-money-bill-wave"></i> المعلومات المالية</h4>
                <div class="summary-item">
                    <span>التكلفة:</span>
                    <strong id="summaryCost">-</strong>
                </div>
                <div class="summary-item">
                    <span>المدفوع:</span>
                    <strong id="summaryPaid">-</strong>
                </div>
                <div class="summary-item">
                    <span>المتبقي:</span>
                    <strong id="summaryCostRest">-</strong>
                </div>
                <div class="summary-item">
                    <span>العملة:</span>
                    <strong id="summaryCurrency">-</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= CONTENT ================= --}}
    <div class="parcels-container">

        {{-- ===== AVAILABLE ===== --}}
        <div class="parcel-search-section">
            <div class="parcel-section-header">
                <h4>
                    <i class="fas fa-search"></i>
                    اختر من الإرساليات المتاحة
                </h4>
            </div>

            <div class="search-box">
                <i class="fas fa-search"></i>
                <input
                    type="text"
                    id="parcelDetailsSearch"
                    placeholder="ابحث برقم الإرسالية، اسم العميل، أو وصف المحتويات…"
                    autocomplete="off"
                >
                <div class="search-loading" id="searchLoading" style="display:none">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>

            <div id="availableParcelsList" class="available-parcels-list">
                <div id="emptySearchState" class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>ابحث عن الإرساليات المتاحة</p>
                    <small>يمكنك البحث برقم الإرسالية أو اسم العميل</small>
                </div>
            </div>
        </div>

        {{-- ===== SELECTED ===== --}}
        <div class="selected-parcels-section">
            <div class="parcel-section-header">
                <h4>
                    <i class="fas fa-list"></i>
                    الإرساليات المختارة
                </h4>
            </div>

            <div id="selectedParcelsList" class="selected-parcels-list">
                <div id="emptySelectedState" class="empty-state-selected">
                    <i class="fas fa-hand-pointer"></i>
                    <p>اسحب الإرساليات من القائمة اليمنى</p>
                    <small>أو اضغط على زر "إضافة"</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ACTIONS ================= --}}
    <div class="step-actions">
        <button type="button" class="btn btn-secondary" data-wizard-prev>
            <i class="fas fa-arrow-right"></i>
            الرجوع
        </button>

        <button type="button" class="btn btn-primary" id="driverParcelSubmitBtn">
            <i class="fas fa-save"></i>
            حفظ الإرسالية
        </button>
    </div>
</div>
