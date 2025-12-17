<div id="step-driver" class="driver tab active">
                <div class="step-header">
                    <h2 class="step-title">
                        <span class="step-icon"><i class="fas fa-user-tie"></i></span>
                        تحديد السائق
                    </h2>
                    <p class="step-description">ابحث عن السائق أو أضف سائقاً جديداً</p>
                </div>
                
                <div class="driver-search-container">
                    <div class="search-header-actions">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="search-driver" placeholder="ابحث بالاسم أو رقم الهاتف..." autocomplete="off">
                            <div class="search-loading" id="driverSearchLoading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="addDriver">
                            <i class="fas fa-plus"></i>
                            إضافة سائق جديد
                        </button>
                    </div>
                    
                    <div class="selected-driver-info" id="selectedDriverInfo" style="display: none;"></div>
                    
                    <div class="driver-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>اسم السائق</th>
                                    <th>رقم الهاتف</th>
                                    <th>تعديل</th>
                                </tr>
                            </thead>
                            <tbody id="driverBody">
                                @include('Drivers.partials.search-states', ['state' => 'initial'])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>