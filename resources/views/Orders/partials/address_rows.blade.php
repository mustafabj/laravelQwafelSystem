@foreach ($addresses as $address)
<tr data-address-id="{{ $address['addressId'] }}" class="address-row">
    <td class="addressTd">{{ $address['city'] ?? '' }}</td>
    <td>{{ $address['area'] ?? '' }}</td>
    <td>{{ $address['street'] ?? '' }}</td>
    <td>{{ $address['buildingNumber'] ?? '' }}</td>
    <td>{{ $address['info'] ?? '' }}</td>
    <td>
        <button type="button" class="btn-edit-address" data-address-id="{{ $address['addressId'] }}">
            تعديل
        </button>
    </td>
</tr>
@endforeach

