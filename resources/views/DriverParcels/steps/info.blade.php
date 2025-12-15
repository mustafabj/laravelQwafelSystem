            <div id="step-info" class="info tab">
                <div class="step-header">
                    <h2 class="step-title">
                        <span class="step-icon">📋</span>
                        المعلومات الأساسية والمالية
                    </h2>
                    <p class="step-description">أكمل المعلومات الأساسية والمالية للإرسالية</p>
                </div>

                <div class="form-sections-container">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-clipboard-list section-icon"></i>
                                المعلومات الأساسية
                            </h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="parcelNumber">
                                    <i class="fas fa-hashtag label-icon"></i>
                                    رقم الإرسالية
                                </label>
                                <input type="number" name="parcelNumber" id="parcelNumber" value="{{ $nextParcelNumber }}" required readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="driverName">
                                    <i class="fas fa-user label-icon"></i>
                                    اسم السائق <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="driverName" id="driverName" required readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="driverNumber">
                                    <i class="fas fa-phone label-icon"></i>
                                    رقم السائق <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="driverNumber" id="driverNumber" required readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="officeId">
                                    <i class="fas fa-building label-icon"></i>
                                    المكتب
                                </label>
                                <select name="officeId" id="officeId" class="form-select" disabled>
                                    <option value="">اختر المكتب</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->officeId }}">{{ $office->officeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-money-bill-wave section-icon"></i>
                                المعلومات المالية
                            </h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cost">
                                    <i class="fas fa-dollar-sign label-icon"></i>
                                    التكلفة
                                </label>
                                <input type="number" name="cost" id="cost" step="0.01" min="0" value="0">
                            </div>

                            <div class="form-group">
                                <label for="paid">
                                    <i class="fas fa-credit-card label-icon"></i>
                                    المدفوع
                                </label>
                                <input type="number" name="paid" id="paid" step="0.01" min="0" value="0">
                            </div>

                            <div class="form-group">
                                <label for="costRest">
                                    <i class="fas fa-chart-line label-icon"></i>
                                    المتبقي
                                </label>
                                <input type="number" name="costRest" id="costRest" step="0.01" min="0" value="0" readonly class="readonly-input">
                            </div>

                            <div class="form-group">
                                <label for="currency">
                                    <i class="fas fa-exchange-alt label-icon"></i>
                                    العملة
                                </label>
                                <select name="currency" id="currency">
                                    <option value="IQD" selected>دينار عراقي</option>
                                    <option value="USD">دولار</option>
                                    <option value="EUR">يورو</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" data-wizard-prev>
                        <i class="fas fa-arrow-right"></i>
                        <span>الرجوع</span>
                    </button>
                    <button type="button" class="btn btn-primary" data-wizard-next>
                        <span>التالي</span>
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>