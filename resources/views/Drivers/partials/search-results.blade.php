@foreach ($drivers as $driver)
    <tr data-id="{{ $driver->driverId }}" style="cursor: pointer;">
        <td class="name">{{ $driver->driverName }}</td>
        <td>{{ $driver->driverPhone }}</td>
        <td>
            <button class="edit" onclick="event.stopPropagation(); App.pages.DriverParcelWizard.DriverStep.editDriver({{ $driver->driverId }})">
                تعديل
            </button>
        </td>
    </tr>
@endforeach

