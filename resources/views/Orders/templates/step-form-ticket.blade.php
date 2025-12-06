<template id="template-step-ticket">

<div class="ticket-form-container">
    <h1 class="form-title">
        <span class="form-icon">✈️</span>
        تذكرة سفر
    </h1>

    <form id="ticketForm" class="ticket-form">

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
                    <label>
                        <span class="label-icon">🔢</span>
                        رقم التذكرة
                    </label>
                    <input type="text" id="ticketId" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">👤</span>
                        اسم المسافر
                    </label>
                    <input type="text" id="travelerName" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">🛂</span>
                        رقم جواز المسافر
                    </label>
                    <input type="text" id="passportNumber" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">📅</span>
                        تاريخ التذكرة
                    </label>
                    <input type="text" id="ticketDate" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">📱</span>
                        هاتف المسافر
                    </label>
                    <input type="text" id="travelerPhone" readonly class="readonly-input">
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">🏠</span>
                        عنوان المنزل
                    </label>
                    <input type="text" id="travelerAddress" readonly class="readonly-input">
                </div>

            </div>

        </div>

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
                    <label>
                        <span class="label-icon">📍</span>
                        جهة السفر
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="travelDestination" required>
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">🪑</span>
                        رقم المقعد
                    </label>
                    <input type="text" id="seatNumber">
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">📅</span>
                        تاريخ السفر
                    </label>
                    <input type="date" id="travelDate">
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">🕐</span>
                        وقت السفر
                    </label>
                    <input type="time" id="travelTime">
                </div>

            </div>
        </div>

        <div class="form-section payment-section">

            <div class="section-header">
                <h3 class="section-title">
                    <span class="section-icon">💳</span>
                    معلومات الدفع
                </h3>
            </div>

            <div class="payment-fields-grid">

                <div class="form-group payment-amount-group">
                    <label>
                        <span class="label-icon">💰</span>
                        سعر التذكرة
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="ticketCost" required min="1" step="0.01" value="1">
                </div>

                <div class="form-group payment-select-group">
                    <label>
                        <span class="label-icon">💱</span>
                        العملة
                    </label>
                    <select id="ticketCurrency">
                        <option value="JD">دينار</option>
                        <option value="USD">دولار</option>
                        <option value="IQD">دينار عراقي</option>
                        <option value="SYP">ليرة سوري</option>
                        <option value="SAR">ريال سعودي</option>
                    </select>
                </div>

                <div class="form-group payment-select-group">
                    <label>
                        <span class="label-icon">✅</span>
                        حالة الدفع
                    </label>
                    <select id="ticketPaidStatus">
                        <option value="paid">مدفوع</option>
                        <option value="unpaid">غير مدفوع</option>
                    </select>
                </div>

            </div>

            <div class="form-row hidden" id="ticketAmountRestRow">
                <div class="form-group form-group-full">
                    <label>
                        <span class="label-icon">💵</span>
                        واصل
                    </label>
                    <input type="number" id="ticketCostRest" min="0" step="0.01" value="0">
                </div>
            </div>

        </div>

    </form>

</div>

</template>
