<div class="parcel-form-container">
    <h1 class="form-title">ارسالية شحن</h1>

    <form id="saveParcel" class="parcel-form">
        {{-- Sender Information (Readonly) --}}
        <div class="form-section">
            <div class="form-row">
                <div class="form-group">
                    <label for="parcelid">رقم الارسالية</label>
                    <input type="text" name="parcelid" id="parcelid" value="{{ $nextParcelNumber }}" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="nameS">اسم المرسل</label>
                    <input type="text" name="nameS" id="nameS" readonly class="readonly-input">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="date">التاريخ</label>
                    <input type="text" name="date" id="date" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label for="phoneS">هاتف المرسل</label>
                    <input type="text" name="phoneS" id="phoneS" readonly class="readonly-input">
                </div>
            </div>
        </div>

        {{-- Recipient Information --}}
        <div class="form-section">
            <h3 class="section-title">معلومات المرسل اليه</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="nameST">اسم المرسل اليه <span class="text-danger">*</span></label>
                    <input type="text" name="nameST" id="nameST" required>
                </div>
                <div class="form-group">
                    <label for="phoneST">هاتف المرسل اليه <span class="text-danger">*</span></label>
                    <input type="text" name="phoneST" id="phoneST" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="addressST">المكان المرسل اليه <span class="text-danger">*</span></label>
                    <input type="text" name="addressST" id="addressST" required>
                </div>
                <div class="form-group">
                    <label for="officeST">المكتب المرسل اليه <span class="text-danger">*</span></label>
                    <select name="officeST" id="officeST" class="form-select" required>
                        <option value="">اختر المكتب</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->officeId }}">{{ $office->officeName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="form-section">
            <h3 class="section-title">معلومات الدفع</h3>
            <div class="form-row">
                <div class="form-group cost-group">
                    <label for="cost">الرسوم <span class="text-danger">*</span></label>
                    <div class="currency-price-group">
                        <input type="number" name="cost" id="cost" min="0" value="0" step="0.01" required>
                        <select name="currency" id="currency" class="currency-select">
                            <option value="JD">دينار</option>
                            <option value="USD">دولار</option>
                            <option value="IQD">دينار عراقي</option>
                            <option value="SYP">ليرة سوري</option>
                            <option value="SAR">ريال سعودي</option>
                        </select>
                        <select name="paid" id="paymentPaid" class="currency-select">
                            <option value="paid">مدفوع</option>
                            <option value="unpaid">غير مدفوع</option>
                            <option value="LaterPaid">تم الدفع لاحقا</option>
                        </select>
                        <select name="paidMethod" id="paymentMethod" class="currency-select">
                            <option value="cash">نقدي</option>
                            <option value="bank">حوالة بنكية</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row hidden" id="paymentPks">
                <div class="form-group cost-group">
                    <label for="costRest">واصل</label>
                    <div class="currency-price-group">
                        <input type="number" name="costRest" id="costRest" min="0" value="0" step="0.01">
                    </div>
                </div>
                @if($currentOffice)
                <div class="form-group">
                    <label class="toggle-container">
                        <span>الدفع في {{ $currentOffice->officeName }}</span>
                        <input type="checkbox" name="paidInMainOffice" id="paidInMainOffice" class="toggle-checkbox">
                        <div class="toggle-switch"></div>
                    </label>
                </div>
                @endif
            </div>
        </div>

        {{-- Package Items --}}
        <div class="form-section">
            <h3 class="section-title">أصناف الارسالية</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="packagequnt">عدد الاصناف</label>
                    <select name="packagequnt" id="packagequnt" class="form-select">
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ $i === 1 ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="packages-details" id="packagesDet">
                <div class="package-detail" data-package-index="1">
                    <h4 class="package-number">الصنف 1</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="qun1">العدد</label>
                            <input type="number" name="qun[]" id="qun1" class="qun-input" min="1" value="1">
                        </div>
                        <div class="form-group">
                            <label for="desc1">الوصف</label>
                            <textarea name="desc[]" id="desc1" class="desc-input" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="button" class="btn-delete-package" onclick="deletePackage(this)">حذف</button>
                </div>
            </div>
        </div>
    </form>
</div>

