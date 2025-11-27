@foreach ($customers as $row)
<tr data-id="{{ $row->customerId }}" onclick="App.pages.OrderWizard.selectCustomer({{ $row->customerId }})">
    <td class="name">{{ $row->FName }} {{ $row->LName }}</td>
    <td class="cusPassport">{{ $row->customerPassport }}</td>
    <td>{{ $row->phone1 }}</td>
    <td>{{ $row->phone2 }}</td>
    <td>{{ $row->phone3 }}</td>
    <td>{{ $row->phone4 }}</td>
    <td>{{ $row->customerState }}</td>

    <td>
        <button class="historyCustomer" onclick="event.stopPropagation(); OrderWizard.historyCustomer({{ $row->customerId }})">
            السجل
        </button>
    </td>

    <td>
        <button class="edit" onclick="event.stopPropagation(); OrderWizard.editCustomer({{ $row->customerId }})">
            تعديل
        </button>
    </td>
</tr>
@endforeach
