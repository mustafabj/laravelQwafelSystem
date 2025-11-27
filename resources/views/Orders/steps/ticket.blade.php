<div class="ticket-form-container">
    <h1 class="form-title">تذكرة سفر</h1>

    <form id="saveTicket" class="ticket-form">
        {{-- Traveler Information (Readonly) --}}
        <div class="form-section">
            <div class="form-row">
                <div class="form-group">
                    <label for="ticketId">رقم التذكرة</label>
                    <input type="text" name="ticketId" id="ticketId" value="{{ $nextTicketNumber }}" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="namec">اسم المسافر</label>
                    <input type="text" name="namec" id="namec" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="namecp">رقم جواز المسافر</label>
                    <input type="text" name="namecp" id="namecp" readonly class="readonly-input">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="datec">تاريخ التذكرة</label>
                    <input type="text" name="datec" id="datec" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="phonec">هاتف المسافر</label>
                    <input type="text" name="phonec" id="phonec" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="addressCust">عنوان المنزل</label>
                    <input type="text" name="addressCust" id="addressCust" readonly class="readonly-input">
                </div>
            </div>
        </div>

        {{-- Travel Information --}}
        <div class="form-section">
            <h3 class="section-title">معلومات السفر</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="TrancustTo">جهة السفر <span class="text-danger">*</span></label>
                    <input type="text" name="TrancustTo" id="TrancustTo" required>
                </div>
                <div class="form-group">
                    <label for="custbn">رقم المقعد</label>
                    <input type="text" name="custbn" id="custbn">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="datact">تاريخ السفر</label>
                    <input type="date" name="datact" id="datact">
                </div>
                <div class="form-group">
                    <label for="timect">وقت السفر</label>
                    <input type="time" name="timect" id="timect">
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="form-section">
            <h3 class="section-title">معلومات الدفع</h3>
            <div class="form-row">
                <div class="form-group cost-group">
                    <label for="cost">سعر التذكرة <span class="text-danger">*</span></label>
                    <div class="currency-price-group">
                        <input type="number" name="cost" id="cost" min="1" value="1" step="0.01" required>
                        <select name="currency" id="currency" class="currency-select">
                            <option value="JD">دينار</option>
                            <option value="USD">دولار</option>
                            <option value="IQD">دينار عراقي</option>
                            <option value="SYP">ليرة سوري</option>
                            <option value="SAR">ريال سعودي</option>
                        </select>
                        <select name="paid" id="paymentStatus" class="currency-select">
                            <option value="paid">مدفوع</option>
                            <option value="unpaid">غير مدفوع</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row hidden" id="paymentAmount">
                <div class="form-group cost-group">
                    <label for="costRest">واصل</label>
                    <div class="currency-price-group">
                        <input type="number" name="costRest" id="costRest" min="0" value="0" step="0.01">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

