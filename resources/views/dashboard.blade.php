@extends('layouts.app')

@section('page-title', 'لوحة التحكم')

@section('content')
    <div class="dashboard-modern">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $totalParcels ?? 0 }}</div>
                    <div class="stat-label">عدد الإرساليات</div>
                    <div class="stat-subtitle">منذ بداية الشهر</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-bus"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $totalTickets ?? 0 }}</div>
                    <div class="stat-label">عدد السفريات</div>
                    <div class="stat-subtitle">خلال هذا الشهر</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $acceptedCount ?? 0 }}</div>
                    <div class="stat-label">المقبولة</div>
                    <div class="stat-subtitle">طلبات مكتملة</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
                    <div class="stat-label">الجديدة</div>
                    <div class="stat-subtitle">بانتظار المعالجة</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="{{ route('wizard') }}" class="action-btn action-primary">
                <i class="fas fa-plus-circle"></i>
                <span>إضافة إرسالية أو تذكرة</span>
            </a>
        </div>

        <!-- Tabs Section -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="parcels">
                    <i class="fas fa-box"></i>
                    <span>الارساليات</span>
                </button>
                <button class="tab-btn" data-tab="tickets">
                    <i class="fas fa-bus"></i>
                    <span>السفريات</span>
                </button>
                <button class="tab-btn" data-tab="driver-parcels">
                    <i class="fas fa-truck"></i>
                    <span>إرساليات السائقين</span>
                </button>
            </div>

            <!-- Parcels Tab -->
            <div class="tab-content active" id="parcels-tab">
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="parcelsSearch" placeholder="ابحث في الارساليات..." onkeyup="searchh()">
                    </div>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all" onclick="handleFilterChange(this)" value="all">
                            <span>الجميع</span>
                        </button>
                        <button class="filter-btn" data-filter="صادر" onclick="handleFilterChange(this)" value="صادر">
                            <span>الصادر</span>
                        </button>
                        <button class="filter-btn" data-filter="وارد" onclick="handleFilterChange(this)" value="وارد">
                            <span>الوارد</span>
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table" id="parcelsTable">
                        <thead>
                            <tr>
                                <th>رقم الارسالية</th>
                                <th>اسم العميل</th>
                                <th>رقم العميل</th>
                                <th>اسم المرسل اليه</th>
                                <th>رقم المرسل اليه</th>
                                <th>اسم الموظف</th>
                                <th>المكتب</th>
                                <th>المكتب المرسل اليه</th>
                                <th>تاريخ الوصل</th>
                                <th>صادر / وارد</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody id="indexParecelsBody">
                            @foreach ($parcels as $parcel)
                                <tr data-parcel-id="{{ $parcel->parcelId }}"
                                    class="{{ $parcel->accept === 'no' ? 'row-pending' : '' }}">
                                    <td><strong>{{ $parcel->parcelNumber }}</strong></td>
                                    <td>{{ $parcel->customer?->FName }} {{ $parcel->customer?->LName }}</td>
                                    <td>{{ $parcel->custNumber }}</td>
                                    <td>{{ $parcel->recipientName }}</td>
                                    <td>{{ $parcel->recipientNumber }}</td>
                                    <td>{{ $parcel->user?->name }}</td>
                                    <td>{{ $parcel->originOffice?->officeName }}</td>
                                    <td>{{ $parcel->destinationOffice?->officeName }}</td>
                                    <td>{{ $parcel->parcelDate }}</td>
                                    <td>
                                        <span class="badge badge-{{ $parcel->status_label === 'صادر' ? 'primary' : 'secondary' }}">
                                            {{ $parcel->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $parcel->accept === 'no' ? 'status-pending' : 'status-accepted' }}">
                                            {{ $parcel->accept === 'no' ? 'جديد' : 'مقبول' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tickets Tab -->
            <div class="tab-content" id="tickets-tab">
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="ticketsSearch" placeholder="ابحث في السفريات..." onkeyup="searchh()">
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table" id="indexTicketTable">
                        <thead>
                            <tr>
                                <th>رقم التذكرة</th>
                                <th>اسم العميل</th>
                                <th>رقم العميل</th>
                                <th>اسم الموظف</th>
                                <th>اسم المكتب</th>
                                <th>السفر من</th>
                                <th>السفر الى</th>
                                <th>تاريخ التذكرة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody id="indexTicketBody">
                            @foreach ($tickets as $ticket)
                                <tr data-ticket-id="{{ $ticket->ticketId }}"
                                    class="{{ $ticket->accept === 'no' ? 'row-pending' : '' }}"
                                    data-id="{{ $ticket->id }}">
                                    <td><strong>{{ $ticket->tecketNumber }}</strong></td>
                                    <td>{{ $ticket->customer?->FName }} {{ $ticket->customer?->LName }}</td>
                                    <td>{{ $ticket->custNumber }}</td>
                                    <td>{{ $ticket->user?->name }}</td>
                                    <td>{{ $ticket->office?->officeName }}</td>
                                    <td>
                                        @php
                                            $address = $ticket->address ?? null;
                                        @endphp
                                        @if ($address && $address->city)
                                            {{ $address->city }} / {{ $address->area }}
                                        @else
                                            <span class="text-muted">لا يوجد عنوان</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->destination }}</td>
                                    <td>{{ $ticket->ticketDate }}</td>
                                    <td>
                                        <span class="status-badge {{ $ticket->accept === 'no' ? 'status-pending' : 'status-accepted' }}">
                                            {{ $ticket->accept === 'no' ? 'جديد' : 'مقبول' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Driver Parcels Tab -->
            <div class="tab-content" id="driver-parcels-tab">
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="driverParcelsSearch" placeholder="ابحث في إرساليات السائقين..." onkeyup="searchh()">
                    </div>
                    <div class="filter-buttons">
                        <a href="{{ route('driver-parcels.create') }}" class="action-btn action-primary" style="margin-right: 10px;">
                            <i class="fas fa-plus-circle"></i>
                            <span>إضافة إرسالية سائق</span>
                        </a>
                        <a href="{{ route('driver-parcels.index') }}" class="action-btn action-secondary">
                            <i class="fas fa-list"></i>
                            <span>عرض الكل</span>
                        </a>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table" id="driverParcelsTable">
                        <thead>
                            <tr>
                                <th>رقم الإرسالية</th>
                                <th>اسم السائق</th>
                                <th>رقم السائق</th>
                                <th>الرحلة</th>
                                <th>تاريخ الرحلة</th>
                                <th>الوجهة</th>
                                <th>المكتب</th>
                                <th>اسم الموظف</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="driverParcelsBody">
                            @forelse ($driverParcels ?? [] as $driverParcel)
                                <tr data-driver-parcel-id="{{ $driverParcel->parcelId }}"
                                    class="{{ $driverParcel->status === 'pending' ? 'row-pending' : '' }}">
                                    <td><strong>{{ $driverParcel->parcelNumber }}</strong></td>
                                    <td>{{ $driverParcel->driverName }}</td>
                                    <td>{{ $driverParcel->driverNumber }}</td>
                                    <td>
                                        @if ($driverParcel->trip)
                                            {{ $driverParcel->trip->tripName }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($driverParcel->tripDate)
                                            {{ $driverParcel->tripDate }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $driverParcel->sendTo }}</td>
                                    <td>{{ $driverParcel->office?->officeName ?? '-' }}</td>
                                    <td>{{ $driverParcel->user?->name ?? '-' }}</td>
                                    <td>{{ $driverParcel->parcelDate }}</td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'pending' => 'status-pending',
                                                'in_transit' => 'status-info',
                                                'arrived' => 'status-success',
                                                'delivered' => 'status-accepted',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'قيد الانتظار',
                                                'in_transit' => 'قيد النقل',
                                                'arrived' => 'وصلت',
                                                'delivered' => 'تم التسليم',
                                            ];
                                            $statusClass = $statusClasses[$driverParcel->status] ?? 'status-pending';
                                            $statusLabel = $statusLabels[$driverParcel->status] ?? $driverParcel->status;
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('driver-parcels.show', $driverParcel->parcelId) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">
                                        لا توجد إرساليات سائقين
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
