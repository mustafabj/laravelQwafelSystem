<template id="template-step-customer">
    <div class="search">
        <div class="container">
            <form id="customerSearchForm" onsubmit="return false;">
                <button type="submit">
                    <img src="{{ asset('image/search.png') }}" alt="search">
                </button>
                <input type="text" id="customerSearchInput" placeholder="ابحث هنا">
            </form>
            <button type="button" id="openAddCustomerModal" class="btn btn-primary">
                اضافة عميل
            </button>
        </div>
    </div>
    <div class="customer-table phoneCall">
        <div class="Table" id="customerTableWrapper">
            <table>
                <thead>
                    <tr>
                        <td>اسم العميل</td>
                        <td>رقم جواز السفر</td>
                        <td>رقم الهاتف</td>
                        <td>الهاتف 2</td>
                        <td>الهاتف 3</td>
                        <td>الهاتف 4</td>
                        <td>حالة العميل</td>
                        <td>السجل</td>
                        <td>تعديل</td>
                    </tr>
                </thead>
                <tbody id="customerTableBody">
                    @include('Orders.templates.partials.customer-empty')
                </tbody>
            </table>
        </div>
    </div>
</template>