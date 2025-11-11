@extends('layouts.app')
@section('content')
    <div class="container mt-3">
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">

            <!-- Total Parcels -->
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <i class="fas fa-box text-primary fa-lg me-2"></i>
                            <h6 class="mb-0 fw-bold">عدد الإرساليات</h6>
                        </div>
                        <h4 class="fw-bold text-dark mb-0">{{ $totalParcels ?? 0 }}</h4>
                        <small class="text-muted">منذ بداية الشهر</small>
                    </div>
                </div>
            </div>

            <!-- Total Tickets -->
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <i class="fas fa-bus text-success fa-lg me-2"></i>
                            <h6 class="mb-0 fw-bold">عدد السفريات</h6>
                        </div>
                        <h4 class="fw-bold text-dark mb-0">{{ $totalTickets ?? 0 }}</h4>
                        <small class="text-muted">خلال هذا الشهر</small>
                    </div>
                </div>
            </div>

            <!-- Accepted -->
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <i class="fas fa-check-circle text-success fa-lg me-2"></i>
                            <h6 class="mb-0 fw-bold">المقبولة</h6>
                        </div>
                        <h4 class="fw-bold text-success mb-0">{{ $acceptedCount ?? 0 }}</h4>
                        <small class="text-muted">طلبات مكتملة</small>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="col-12 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <i class="fas fa-hourglass-half text-warning fa-lg me-2"></i>
                            <h6 class="mb-0 fw-bold">الجديدة</h6>
                        </div>
                        <h4 class="fw-bold text-warning mb-0">{{ $pendingCount ?? 0 }}</h4>
                        <small class="text-muted">بانتظار المعالجة</small>
                    </div>
                </div>
            </div>

        </div>
        <ul class="nav nav-tabs historyTabs" id="parcelTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active historyTab" id="historyT-tab" data-bs-toggle="tab"
                    data-bs-target="#historyT" type="button" role="tab" aria-controls="historyT"
                    aria-selected="true">
                    الارساليات
                </button>
            </li>
            {{-- <li class="nav-item" role="presentation">
                <button class="nav-link historyTab" id="historyTrip-tab" data-bs-toggle="tab"
                    data-bs-target="#historyTrip" type="button" role="tab" aria-controls="historyTrip"
                    aria-selected="false">
                    ارساليات السائقين
                </button>
            </li> --}}
            <li class="nav-item" role="presentation">
                <button class="nav-link historyTab" id="historyS-tab" data-bs-toggle="tab" data-bs-target="#historyS"
                    type="button" role="tab" aria-controls="historyS" aria-selected="false">
                    السفريات
                </button>
            </li>


        </ul>
    </div>
    <!-- Start Search -->
    <div class="scroll-to-top  noPrint">
        <span class="up"><img src="image/top.png" alt="top" /></span>
    </div>

    <!-- End Search -->
    <!-- Start history -->
    <div class="history noPrint">
        <div class="container">
            <div class="tab-content">
                <div class="tab-pane fade show active historyT" id="historyT" role="tabpanel"
                    aria-labelledby="historyT-tab">
                    <div class="search noPrint">
                        <div class="container">
                            <form action="">
                                <img src="image/search.png" alt="search" />
                                <input onkeyup="searchh()" type="text" id="search" placeholder="ابحث هنا" />
                            </form>
                            <label for="filterAll">
                                <input type="radio" class="filterHome" name="filterHome" id="filterAll"
                                    onchange="handleFilterChange(this)" value="all" checked>
                                <div class="filterHomeLabel">الجميع</div>
                            </label>
                            <label for="filterCome">
                                <input type="radio" class="filterHome" name="filterHome" id="filterCome"
                                    onchange="handleFilterChange(this)" value="صادر">
                                <div class="filterHomeLabel">الصادر</div>
                
                            </label>
                            <label for="filterSend">
                                <input type="radio" class="filterHome" name="filterHome" id="filterSend"
                                    onchange="handleFilterChange(this)" value="وارد">
                                <div class="filterHomeLabel">الوارد</div>
                            </label>
                        </div>
                    </div>
                    <table class="myTable" id="parcelsTable">
                        <thead>
                            <tr>
                                <td>رقم الارسالية</td>
                                <td>اسم العميل</td>
                                <td>رقم العميل</td>
                                <td>اسم المرسل اليه</td>
                                <td>رقم المرسل اليه</td>
                                <td>اسم الموظف</td>
                                <td>المكتب</td>
                                <td>المكتب المرسل اليه</td>
                                <td>تاريخ الوصل</td>
                                <td>صادر / وارد</td>
                                <td>الحالة</td>
                            </tr>
                        </thead>
                        <tbody id="indexParecelsBody">
                            @foreach ($parcels as $parcel)
                                <tr data-parcel-id="{{ $parcel->parcelId }}"
                                    class="{{ $parcel->accept === 'no' ? 'notAccept' : '' }}">
                                    <td class="name">{{ $parcel->parcelNumber }}</td>
                                    <td class="name">{{ $parcel->customer?->FName }} {{ $parcel->customer?->LName }}</td>
                                    <td>{{ $parcel->custNumber }}</td>
                                    <td>{{ $parcel->recipientName }}</td>
                                    <td>{{ $parcel->recipientNumber }}</td>
                                    <td>{{ $parcel->user?->name }}</td>
                                    <td>{{ $parcel->originOffice?->officeName }}</td>
                                    <td>{{ $parcel->destinationOffice?->officeName }}</td>
                                    <td>{{ $parcel->parcelDate }}</td>
                                    <td>{{ $parcel->status_label }}</td>
                                    <td>{{ $parcel->accept === 'no' ? 'جديد' : 'مقبول' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade historyS" id="historyS" role="tabpanel" aria-labelledby="historyS-tab">
                    <div class="search noPrint">
                        <div class="container">
                            <form action="">
                                <img src="image/search.png" alt="search" />
                                <input onkeyup="searchh()" type="text" id="search" placeholder="ابحث هنا" />
                            </form>
                        </div>
                    </div>
                    <table class="myTable" id="indexTicketTable">
                        <thead>
                            <tr>
                                <td>رقم التذكرة</td>
                                <td>اسم العميل</td>
                                <td>رقم العميل</td>
                                <td>اسم الموظف</td>
                                <td>اسم المكتب</td>
                                <td>السفر من</td>
                                <td>السفر الى</td>
                                <td>تاريخ التذكرة</td>
                                <td>الحالة</td>
                            </tr>
                        </thead>
                        <tbody id="indexTicketBody">
                            @foreach ($tickets as $ticket)
                                <tr data-ticket-id="{{ $ticket->ticketId }}"
                                    class="{{ $ticket->accept === 'no' ? 'notAccept' : '' }}"
                                    data-id="{{ $ticket->id }}">
                                    <td class="name">{{ $ticket->tecketNumber }}</td>
                                    <td class="name">{{ $ticket->customer?->FName }} {{ $ticket->customer?->LName }}
                                    </td>
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
                                            لا يوجد عنوان
                                        @endif
                                    </td>
                                    <td>{{ $ticket->destination }}</td>
                                    <td>{{ $ticket->ticketDate }}</td>
                                    <td>{{ $ticket->accept === 'no' ? 'جديد' : 'مقبول' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="historyTrip" role="tabpanel" aria-labelledby="historyTrip-tab">
                    <h5>قائمة السفريات</h5>
                    <!-- Content for third tab -->
                </div>

            </div>
        </div>
    </div>
@endsection
