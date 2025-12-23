@extends('layouts.app')

@section('page-title', 'إدارة الرحلات والموافقات')

@section('content')
<div class="trip-management-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-route"></i>
            إدارة الرحلات والموافقات على نقاط التوقف
        </h1>
    </div>

    @if($allPendingArrivals->count() > 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            يوجد {{ $allPendingArrivals->count() }} طلب موافقة في انتظار المراجعة
        </div>
    @endif

    <div class="trips-list">
        @forelse($trips as $trip)
            <div class="trip-card">
                <div class="trip-header">
                    <div class="trip-title-section">
                        <h3 class="trip-name">
                            <i class="fas fa-route"></i>
                            {{ $trip->tripName }}
                        </h3>
                        <div class="trip-meta">
                            <span class="trip-destination">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $trip->destination }}
                            </span>
                            <span class="trip-office">
                                <i class="fas fa-building"></i>
                                {{ $trip->office->officeName ?? 'غير محدد' }}
                            </span>
                        </div>
                    </div>
                    <div class="trip-status">
                        <span class="badge badge-warning">
                            {{ $trip->pendingArrivals->count() }} طلب موافقة
                        </span>
                    </div>
                </div>

                <div class="pending-arrivals-section">
                    <h4 class="section-title">
                        <i class="fas fa-clock"></i>
                        طلبات الموافقة المعلقة
                    </h4>

                    <div class="arrivals-list">
                        @foreach($trip->pendingArrivals as $arrival)
                            <div class="arrival-card" data-arrival-id="{{ $arrival->arrivalId }}">
                                <div class="arrival-header">
                                    <div class="arrival-info">
                                        <strong>
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $arrival->stopPoint->stopName ?? 'نقطة غير محددة' }}
                                        </strong>
                                        <span class="arrival-time">
                                            <i class="fas fa-clock"></i>
                                            متوقع: {{ $arrival->expectedArrivalTime ? \Carbon\Carbon::parse($arrival->expectedArrivalTime)->format('Y-m-d H:i') : '-' }}
                                        </span>
                                        @if($arrival->arrivedAt)
                                            <span class="arrival-time">
                                                <i class="fas fa-check"></i>
                                                وصل: {{ \Carbon\Carbon::parse($arrival->arrivedAt)->format('Y-m-d H:i') }}
                                            </span>
                                        @endif
                                        <span class="arrival-request-time">
                                            <i class="fas fa-hourglass-half"></i>
                                            طلب الموافقة: {{ $arrival->requestedAt ? \Carbon\Carbon::parse($arrival->requestedAt)->diffForHumans() : '-' }}
                                        </span>
                                    </div>
                                    <div class="arrival-actions">
                                        <button class="btn btn-success btn-sm approve-btn" 
                                                data-arrival-id="{{ $arrival->arrivalId }}"
                                                data-stop-name="{{ $arrival->stopPoint->stopName ?? '' }}">
                                            <i class="fas fa-check"></i> موافقة
                                        </button>
                                        <button class="btn btn-danger btn-sm reject-btn" 
                                                data-arrival-id="{{ $arrival->arrivalId }}">
                                            <i class="fas fa-times"></i> رفض
                                        </button>
                                    </div>
                                </div>

                                <div class="arrival-details">
                                    <div class="detail-row">
                                        <span class="detail-label">إرسالية السائق:</span>
                                        <span class="detail-value">#{{ $arrival->driverParcel->parcelNumber ?? '-' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">السائق:</span>
                                        <span class="detail-value">{{ $arrival->driverParcel->driverName ?? '-' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">المكتب:</span>
                                        <span class="detail-value">{{ $arrival->driverParcel->office->officeName ?? '-' }}</span>
                                    </div>
                                </div>

                                <!-- Approval Modal (hidden by default) -->
                                <div class="approval-modal" id="approvalModal{{ $arrival->arrivalId }}" style="display: none;">
                                    <div class="modal-content">
                                        <h4>الموافقة على الوصول</h4>
                                        <form class="approval-form" data-arrival-id="{{ $arrival->arrivalId }}">
                                            @csrf
                                            <div class="form-group">
                                                <label>
                                                    <input type="radio" name="onTime" value="1" checked> وصل في الوقت المحدد
                                                </label>
                                                <label>
                                                    <input type="radio" name="onTime" value="0"> تأخر
                                                </label>
                                            </div>
                                            <div class="form-group">
                                                <label>تعليق (سيظهر للعميل):</label>
                                                <textarea name="comment" rows="3" placeholder="أضف تعليقاً (اختياري)"></textarea>
                                            </div>
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check"></i> موافقة
                                                </button>
                                                <button type="button" class="btn btn-secondary cancel-btn">
                                                    إلغاء
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Reject Modal (hidden by default) -->
                                <div class="reject-modal" id="rejectModal{{ $arrival->arrivalId }}" style="display: none;">
                                    <div class="modal-content">
                                        <h4>رفض الوصول</h4>
                                        <form class="reject-form" data-arrival-id="{{ $arrival->arrivalId }}">
                                            @csrf
                                            <div class="form-group">
                                                <label>تعليق (مطلوب - سيظهر للعميل):</label>
                                                <textarea name="comment" rows="3" required placeholder="أضف تعليقاً يوضح سبب الرفض"></textarea>
                                            </div>
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times"></i> رفض
                                                </button>
                                                <button type="button" class="btn btn-secondary cancel-btn">
                                                    إلغاء
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>لا توجد طلبات موافقة معلقة</h3>
                <p>جميع طلبات الموافقة تم التعامل معها</p>
            </div>
        @endforelse
    </div>
</div>

<style>
.trip-management-container {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.trip-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.trip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.trip-name {
    margin: 0 0 10px 0;
    color: #333;
}

.trip-meta {
    display: flex;
    gap: 20px;
    color: #666;
}

.pending-arrivals-section {
    margin-top: 20px;
}

.section-title {
    margin-bottom: 15px;
    color: #333;
}

.arrivals-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.arrival-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-right: 4px solid #ffc107;
}

.arrival-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.arrival-info {
    flex: 1;
}

.arrival-info strong {
    display: block;
    margin-bottom: 8px;
    color: #333;
}

.arrival-time, .arrival-request-time {
    display: inline-block;
    margin-left: 15px;
    color: #666;
    font-size: 14px;
}

.arrival-actions {
    display: flex;
    gap: 10px;
}

.arrival-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
}

.detail-label {
    font-weight: 500;
    color: #666;
}

.detail-value {
    color: #333;
}

.approval-modal, .reject-modal {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.modal-content h4 {
    margin-top: 0;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 10px;
}

.empty-state i {
    font-size: 64px;
    color: #28a745;
    margin-bottom: 20px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve button click
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const arrivalId = this.dataset.arrivalId;
            const modal = document.getElementById('approvalModal' + arrivalId);
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
        });
    });

    // Reject button click
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const arrivalId = this.dataset.arrivalId;
            const modal = document.getElementById('rejectModal' + arrivalId);
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
        });
    });

    // Cancel button
    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.approval-modal, .reject-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Approve form submit
    document.querySelectorAll('.approval-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const arrivalId = this.dataset.arrivalId;
            const formData = new FormData(this);
            
            fetch(`/admin/trip-management/arrivals/${arrivalId}/approve`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم الموافقة بنجاح');
                    location.reload();
                } else {
                    alert('حدث خطأ: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء المعالجة');
            });
        });
    });

    // Reject form submit
    document.querySelectorAll('.reject-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const arrivalId = this.dataset.arrivalId;
            const formData = new FormData(this);
            
            fetch(`/admin/trip-management/arrivals/${arrivalId}/reject`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم الرفض بنجاح');
                    location.reload();
                } else {
                    alert('حدث خطأ: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء المعالجة');
            });
        });
    });
});
</script>
@endsection

