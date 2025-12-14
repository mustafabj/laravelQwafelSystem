class Trips {
    constructor() {
        this.form = null;
        this.selectedDays = new Set();
        this.times = {};
        this.stopPoints = [];
        this.initialized = false;
    }

    init() {
        if (this.initialized) {
            if (App.config.debug) {
                console.log('[Trips] Already initialized');
            }
            return;
        }

        this.form = document.getElementById('tripForm');
        if (!this.form) {
            return;
        }

        this.bindEvents();
        this.initStopPoints();
        this.initialized = true;

        if (App.config.debug) {
            console.log('[Trips] Initialized');
        }
    }

    bindEvents() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Day checkboxes
        const dayCheckboxes = document.querySelectorAll('.day-checkbox');
        dayCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => this.handleDayChange(e));
        });

        // Add stop point button
        const addStopPointBtn = document.getElementById('addStopPointBtn');
        if (addStopPointBtn) {
            addStopPointBtn.addEventListener('click', () => this.addStopPoint());
        }
    }

    initStopPoints() {
        const stopPointsList = document.getElementById('stopPointsList');
        if (!stopPointsList) {
            return;
        }

        // Handle remove stop point
        stopPointsList.addEventListener('click', (e) => {
            if (e.target.closest('.btn-remove-stop-point')) {
                const btn = e.target.closest('.btn-remove-stop-point');
                const index = parseInt(btn.dataset.index);
                this.removeStopPoint(index);
            }
        });
    }

    addStopPoint() {
        const stopPoint = {
            stopName: '',
            arrivalTime: '',
        };
        this.stopPoints.push(stopPoint);
        this.renderStopPoints();
    }

    removeStopPoint(index) {
        this.stopPoints.splice(index, 1);
        this.renderStopPoints();
    }

    renderStopPoints() {
        const stopPointsList = document.getElementById('stopPointsList');
        if (!stopPointsList) {
            return;
        }

        stopPointsList.innerHTML = '';

        if (this.stopPoints.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'empty-stop-points';
            emptyState.innerHTML = `
                <i class="fas fa-map-marker-alt"></i>
                <p>لا توجد نقاط توقف</p>
                <small>اضغط على "إضافة نقطة توقف" لإضافة نقاط التوقف</small>
            `;
            stopPointsList.appendChild(emptyState);
            return;
        }

        this.stopPoints.forEach((stopPoint, index) => {
            const stopPointItem = document.createElement('div');
            stopPointItem.className = 'stop-point-item';
            stopPointItem.innerHTML = `
                <div class="stop-point-header">
                    <span class="stop-point-number">نقطة توقف #${index + 1}</span>
                    <button type="button" class="btn-remove-stop-point" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="stop-point-fields">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-map-marker-alt"></i>
                            اسم نقطة التوقف <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="stop-point-name" 
                               data-index="${index}"
                               placeholder="مثال: كركوك"
                               value="${stopPoint.stopName}">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-clock"></i>
                            وقت الوصول <span class="text-danger">*</span>
                        </label>
                        <input type="time" 
                               class="stop-point-time" 
                               data-index="${index}"
                               value="${stopPoint.arrivalTime}">
                    </div>
                </div>
            `;
            stopPointsList.appendChild(stopPointItem);

            // Add event listeners
            const nameInput = stopPointItem.querySelector('.stop-point-name');
            const timeInput = stopPointItem.querySelector('.stop-point-time');
            
            nameInput.addEventListener('input', (e) => {
                this.stopPoints[index].stopName = e.target.value;
            });
            
            timeInput.addEventListener('change', (e) => {
                this.stopPoints[index].arrivalTime = e.target.value;
            });
        });
    }

    handleDayChange(e) {
        const checkbox = e.target;
        const day = checkbox.dataset.day;
        const timeInputGroup = document.querySelector(`.time-input-group[data-day="${day}"]`);

        if (checkbox.checked) {
            this.selectedDays.add(day);
            if (timeInputGroup) {
                timeInputGroup.classList.remove('hidden');
                const timeInput = timeInputGroup.querySelector('input[type="time"]');
                if (timeInput && !timeInput.value) {
                    timeInput.value = '08:00'; // Default time
                }
            }
        } else {
            this.selectedDays.delete(day);
            if (timeInputGroup) {
                timeInputGroup.classList.add('hidden');
                const timeInput = timeInputGroup.querySelector('input[type="time"]');
                if (timeInput) {
                    this.times[day] = null;
                }
            }
        }

        this.updateTimesContainer();
    }

    updateTimesContainer() {
        const timesContainer = document.getElementById('timesContainer');
        if (!timesContainer) {
            return;
        }

        timesContainer.innerHTML = '';

        const days = {
            'Sunday': 'الأحد',
            'Monday': 'الإثنين',
            'Tuesday': 'الثلاثاء',
            'Wednesday': 'الأربعاء',
            'Thursday': 'الخميس',
            'Friday': 'الجمعة',
            'Saturday': 'السبت'
        };

        this.selectedDays.forEach(day => {
            const timeGroup = document.createElement('div');
            timeGroup.className = 'time-input-group';
            timeGroup.setAttribute('data-day', day);
            timeGroup.innerHTML = `
                <label class="time-day-label">${days[day]}:</label>
                <div class="time-input-wrapper">
                    <input type="time" 
                           name="times[${day}]" 
                           class="time-input" 
                           data-day="${day}"
                           value="${this.times[day] || '08:00'}"
                           required>
                </div>
            `;
            timesContainer.appendChild(timeGroup);

            // Add event listener for time change
            const timeInput = timeGroup.querySelector('.time-input');
            timeInput.addEventListener('change', (e) => {
                this.times[day] = e.target.value;
            });
        });
    }

    async handleSubmit(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

        try {
            const formData = new FormData(this.form);
            
            // Build data object
            const data = {
                tripName: formData.get('tripName'),
                officeId: formData.get('officeId'),
                destination: formData.get('destination'),
                daysOfWeek: formData.getAll('daysOfWeek[]'),
                times: {},
                isActive: formData.get('isActive') === '1',
                notes: formData.get('notes') || null,
            };

            // Collect times for selected days
            const timeInputs = document.querySelectorAll('.time-input');
            timeInputs.forEach(input => {
                const day = input.dataset.day;
                if (input.value) {
                    data.times[day] = input.value;
                }
            });

            // Collect stop points
            data.stopPoints = this.stopPoints.filter(sp => sp.stopName && sp.arrivalTime);
            
            // Collect final arrival time
            const finalArrivalTimeInput = document.getElementById('finalArrivalTime');
            if (finalArrivalTimeInput && finalArrivalTimeInput.value) {
                data.finalArrivalTime = finalArrivalTimeInput.value;
            }

            const response = await fetch(this.form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage(result.message, 'success');
                setTimeout(() => {
                    window.location.href = '/trips';
                }, 1500);
            } else {
                this.showMessage(result.message || 'حدث خطأ أثناء الحفظ', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            this.showMessage('حدث خطأ أثناء الحفظ', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    showMessage(message, type = 'info') {
        const existingMessages = document.querySelectorAll('.trip-message');
        existingMessages.forEach(msg => msg.remove());

        const messageDiv = document.createElement('div');
        messageDiv.className = `trip-message message-${type}`;
        messageDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

        const form = document.getElementById('tripForm');
        if (form) {
            form.insertBefore(messageDiv, form.firstChild);
            
            setTimeout(() => {
                messageDiv.style.opacity = '1';
            }, 10);

            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }
    }
}

// Export and attach to App
if (typeof App !== 'undefined') {
    if (!App.pages) {
        App.pages = {};
    }
    App.pages.Trips = new Trips();
    
    // Auto-initialize if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            App.pages.Trips.init();
        });
    } else {
        App.pages.Trips.init();
    }
}

export default App.pages.Trips;

