<div class="ticket-form-container">
    <h1 class="form-title">
        <span class="form-icon">✈️</span>
        تذكرة سفر
    </h1>

    <form id="saveTicket" class="ticket-form">
        {{-- Traveler Information (Readonly) --}}
        <div class="form-section sender-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">👤</span>
                    معلومات المسافر
                </h3>
                <span class="section-badge readonly-badge">معلومات جاهزة</span>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="ticketId">
                        <span class="label-icon">🔢</span>
                        رقم التذكرة
                    </label>
                    <input type="text" name="ticketId" id="ticketId" value="{{ $nextTicketNumber }}" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="namec">
                        <span class="label-icon">👤</span>
                        اسم المسافر
                    </label>
                    <input type="text" name="namec" id="namec" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="namecp">
                        <span class="label-icon">🛂</span>
                        رقم جواز المسافر
                    </label>
                    <input type="text" name="namecp" id="namecp" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="datec">
                        <span class="label-icon">📅</span>
                        تاريخ التذكرة
                    </label>
                    <input type="text" name="datec" id="datec" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="phonec">
                        <span class="label-icon">📱</span>
                        هاتف المسافر
                    </label>
                    <input type="text" name="phonec" id="phonec" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="addressCust">
                        <span class="label-icon">🏠</span>
                        عنوان المنزل
                    </label>
                    <input type="text" name="addressCust" id="addressCust" readonly class="readonly-input">
                </div>
            </div>
        </div>

        {{-- Travel Information --}}
        <div class="form-section recipient-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">✈️</span>
                    معلومات السفر
                </h3>
                <span class="section-badge required-badge">مطلوب</span>
            </div>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label for="TrancustTo">
                        <span class="label-icon">📍</span>
                        جهة السفر
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="TrancustTo" id="TrancustTo" required placeholder="أدخل جهة السفر">
                </div>
                <div class="form-group">
                    <label for="custbn">
                        <span class="label-icon">🪑</span>
                        رقم المقعد
                    </label>
                    <input type="text" name="custbn" id="custbn" placeholder="أدخل رقم المقعد">
                </div>
                <div class="form-group">
                    <label for="datact">
                        <span class="label-icon">📅</span>
                        تاريخ السفر
                    </label>
                    <input type="date" name="datact" id="datact">
                </div>
                <div class="form-group">
                    <label for="timect">
                        <span class="label-icon">🕐</span>
                        وقت السفر
                    </label>
                    <input type="time" name="timect" id="timect">
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="form-section payment-section">
            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">💳</span>
                    معلومات الدفع
                </h3>
            </div>
            <div class="payment-fields-grid">
                <div class="form-group payment-amount-group">
                    <label for="cost">
                        <span class="label-icon">💰</span>
                        سعر التذكرة
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="cost" id="cost" min="1" value="1" step="0.01" required placeholder="0.00" class="payment-input">
                </div>
                <div class="form-group payment-select-group">
                    <label for="currency">
                        <span class="label-icon">💱</span>
                        العملة
                    </label>
                    <select name="currency" id="currency" class="currency-select">
                        <option value="JD">دينار</option>
                        <option value="USD">دولار</option>
                        <option value="IQD">دينار عراقي</option>
                        <option value="SYP">ليرة سوري</option>
                        <option value="SAR">ريال سعودي</option>
                    </select>
                </div>
                <div class="form-group payment-select-group">
                    <label for="paymentStatus">
                        <span class="label-icon">✅</span>
                        حالة الدفع
                    </label>
                    <select name="paid" id="paymentStatus" class="currency-select">
                        <option value="paid">مدفوع</option>
                        <option value="unpaid">غير مدفوع</option>
                    </select>
                </div>
            </div>
            <div class="form-row hidden" id="paymentAmount">
                <div class="form-group form-group-full">
                    <label for="costRest">
                        <span class="label-icon">💵</span>
                        واصل
                    </label>
                    <div class="currency-price-group">
                        <input type="number" name="costRest" id="costRest" min="0" value="0" step="0.01" placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
