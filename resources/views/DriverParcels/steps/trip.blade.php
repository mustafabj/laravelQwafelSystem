<div id="step-trip" class="trip tab">
                <div class="step-header">
                    <h2 class="step-title">
                        <span class="step-icon">🚌</span>
                        اختيار الرحلة
                    </h2>
                    <p class="step-description">اختر الرحلة المرتبطة بهذه الإرسالية</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="tripId">
                            <i class="fas fa-bus label-icon"></i>
                            الرحلة <span class="text-danger">*</span>
                        </label>
                        <select name="tripId" id="tripId" required>
                            <option value="">اختر الرحلة</option>
                            @foreach ($trips as $trip)
                                <option value="{{ $trip->tripId }}" 
                                        data-destination="{{ $trip->destination }}"
                                        data-office-id="{{ $trip->officeId }}">
                                    {{ $trip->tripName }} ({{ $trip->destination }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tripDate">
                            <i class="fas fa-calendar label-icon"></i>
                            تاريخ الرحلة <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tripDate" id="tripDate" required>
                    </div>

                    <div class="form-group">
                        <label for="sendTo">
                            <i class="fas fa-map-marker-alt label-icon"></i>
                            الوجهة
                        </label>
                        <input type="text" name="sendTo" id="sendTo" readonly class="readonly-input">
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