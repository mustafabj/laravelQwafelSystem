@extends('layouts.app')
@section('content')
<!-- Start Search -->
<div class="scroll-to-top  noPrint">
    <span class="up"><img src="image/top.png" alt="top" /></span>
</div>
<div class="search noPrint">
    <div class="container">
        <div class="titleHome">
            <h1>اخر الارساليات والسفريات</h1>
            <div style="
                    display: flex;
                    flex-direction: column;
                    gap: 10px;">
                <a href="order.php" class="parcelsButton"> الارساليات والسفريات</a>
                <a href="drivers.php" class="parcelsButton" style="background-color: dimgray;">اخر ارساليات السائقين
                </a>
            </div>
        </div>
    </div>
    <div class="container">
        <form action="">
            <img src="image/search.png" alt="search" />
            <input onkeyup="searchh()" type="text" id="search" placeholder="ابحث هنا" />
        </form>
        <label for="filterAll">
            <input type="radio" class="filterHome" name="filterHome" id="filterAll" onchange="handleFilterChange(this)"
                value="all" checked>
            <div class="filterHomeLabel">الجميع</div>
        </label>
        <label for="filterCome">
            <input type="radio" class="filterHome" name="filterHome" id="filterCome" onchange="handleFilterChange(this)"
                value="صادر">
            <div class="filterHomeLabel">الصادر</div>

        </label>
        <label for="filterSend">
            <input type="radio" class="filterHome" name="filterHome" id="filterSend" onchange="handleFilterChange(this)"
                value="وارد">
            <div class="filterHomeLabel">الوارد</div>
        </label>
    </div>
</div>
<!-- End Search -->
<!-- Start history -->
<div class="history noPrint">
    <div class="container">
        <div class="historyTabs">
            <button class="historyTab historyTabActive" data-cont=".historyT">
                الارساليات
            </button>
            <button class="historyTab" data-cont=".historyS">السفريات</button>
        </div>

        <div class="historyT">
            <table class="myTable">
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


                </tbody>
            </table>
        </div>
        <div class="historyS" style="display: none">
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

                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

