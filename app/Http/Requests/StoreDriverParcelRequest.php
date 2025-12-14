<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverParcelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'parcelNumber' => ['required', 'integer'],
            'tripId' => ['required', 'exists:trips,tripId'],
            'tripDate' => ['required', 'date'],
            'driverName' => ['required', 'string', 'max:255'],
            'driverNumber' => ['required', 'string', 'max:25'],
            'sendTo' => ['required', 'string', 'max:255'],
            'officeId' => ['required', 'integer', 'exists:office,officeId'],
            'cost' => ['nullable', 'numeric'],
            'paid' => ['nullable', 'numeric'],
            'costRest' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'parcelDetails' => ['required', 'array', 'min:1'],
            'parcelDetails.*.parcelDetailId' => ['required', 'exists:parcelsdetails,detailId'],
            'parcelDetails.*.quantityTaken' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'parcelNumber.required' => 'رقم الإرسالية مطلوب',
            'tripId.required' => 'يجب اختيار الرحلة',
            'tripId.exists' => 'الرحلة المحددة غير موجودة',
            'tripDate.required' => 'تاريخ الرحلة مطلوب',
            'tripDate.date' => 'تاريخ الرحلة غير صحيح',
            'driverName.required' => 'اسم السائق مطلوب',
            'driverNumber.required' => 'رقم السائق مطلوب',
            'sendTo.required' => 'عنوان الإرسال مطلوب',
            'officeId.required' => 'يجب اختيار المكتب',
            'parcelDetails.required' => 'يجب إضافة عناصر على الأقل',
            'parcelDetails.min' => 'يجب إضافة عنصر واحد على الأقل',
            'parcelDetails.*.parcelDetailId.required' => 'يجب اختيار تفاصيل الإرسالية',
            'parcelDetails.*.quantityTaken.required' => 'الكمية مطلوبة',
            'parcelDetails.*.quantityTaken.min' => 'الكمية يجب أن تكون أكبر من صفر',
        ];
    }
}

