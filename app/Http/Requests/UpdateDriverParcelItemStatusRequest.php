<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverParcelItemStatusRequest extends FormRequest
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
            'detailId' => ['required', 'exists:driverparceldetails,detailId'],
            'isArrived' => ['required', 'boolean'],
            'delayReason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'detailId.required' => 'معرف التفاصيل مطلوب',
            'detailId.exists' => 'التفاصيل المحددة غير موجودة',
            'isArrived.required' => 'حالة الوصول مطلوبة',
            'isArrived.boolean' => 'حالة الوصول غير صحيحة',
        ];
    }
}

