<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
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
        $driverId = $this->route('driver');

        return [
            'driverName' => ['required', 'string', 'max:255', Rule::unique('driver', 'driverName')->ignore($driverId, 'driverId')],
            'driverPhone' => ['required', 'string', 'max:50', Rule::unique('driver', 'driverPhone')->ignore($driverId, 'driverId')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'driverName.required' => 'اسم السائق مطلوب.',
            'driverName.string' => 'اسم السائق يجب أن يكون نصاً.',
            'driverName.max' => 'اسم السائق يجب ألا يتجاوز 255 حرفاً.',
            'driverName.unique' => 'اسم السائق هذا مسجل بالفعل.',
            'driverPhone.required' => 'رقم هاتف السائق مطلوب.',
            'driverPhone.string' => 'رقم هاتف السائق يجب أن يكون نصاً.',
            'driverPhone.max' => 'رقم هاتف السائق يجب ألا يتجاوز 50 حرفاً.',
            'driverPhone.unique' => 'رقم هاتف السائق هذا مسجل بالفعل.',
        ];
    }
}
