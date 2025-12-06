<template id="template-step-address">

    <div class="address-step-container">

        <div class="address-table-container">
            <table id="wizardAddressTable">
                <thead>
                    <tr>
                        <td>المدينة</td>
                        <td>المنطقة</td>
                        <td>الشارع</td>
                        <td>رقم المنزل</td>
                        <td>تفاصيل العنوان</td>
                        <td>تعديل</td>
                    </tr>
                </thead>
                <tbody id="wizardAddressTableBody">
                    @include('Orders.templates.partials.address-empty')
                </tbody>
            </table>
        </div>

    </div>

</template>
