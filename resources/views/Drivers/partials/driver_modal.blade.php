<div class="modal-header">
    <h5 class="modal-title">إضافة سائق جديد</h5>
    <button type="button" class="btn-close" id="closeDriverModal" aria-label="Close"></button>
</div>
<form id="addDriverForm">
    <div class="modal-body">
        <div class="form-grid">
            <div class="form-group">
                <label for="driverName">
                    <i class="fas fa-user label-icon"></i>
                    اسم السائق <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="driverName" name="driverName" required>
            </div>

            <div class="form-group">
                <label for="driverPhone">
                    <i class="fas fa-phone label-icon"></i>
                    رقم الهاتف <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="driverPhone" name="driverPhone" required>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelDriverBtn">إلغاء</button>
        <button type="submit" class="btn btn-primary" id="submitDriverBtn">
            <i class="fas fa-save"></i>
            حفظ السائق
        </button>
    </div>
</form>

