<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
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
            'tripName' => 'required|string|max:255',
            'officeId' => 'required|exists:office,officeId',
            'destination' => 'required|string|max:255',
            'daysOfWeek' => 'required|array|min:1',
            'daysOfWeek.*' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'times' => 'required|array',
            'times.*' => ['nullable', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'stopPoints' => 'nullable|array',
            'stopPoints.*.stopName' => 'required_with:stopPoints|string|max:255',
            'stopPoints.*.arrivalTime' => ['required_with:stopPoints', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'finalArrivalTime' => ['nullable', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'isActive' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tripName.required' => 'اسم الرحلة مطلوب.',
            'tripName.string' => 'اسم الرحلة يجب أن يكون نصاً.',
            'tripName.max' => 'اسم الرحلة يجب ألا يتجاوز 255 حرفاً.',
            'officeId.required' => 'المكتب مطلوب.',
            'officeId.exists' => 'المكتب المحدد غير موجود.',
            'destination.required' => 'الوجهة مطلوبة.',
            'destination.string' => 'الوجهة يجب أن تكون نصاً.',
            'destination.max' => 'الوجهة يجب ألا تتجاوز 255 حرفاً.',
            'daysOfWeek.required' => 'يجب اختيار يوم واحد على الأقل.',
            'daysOfWeek.array' => 'أيام الأسبوع يجب أن تكون مصفوفة.',
            'daysOfWeek.min' => 'يجب اختيار يوم واحد على الأقل.',
            'daysOfWeek.*.required' => 'يوم الأسبوع مطلوب.',
            'daysOfWeek.*.in' => 'يوم الأسبوع غير صالح.',
            'times.required' => 'الأوقات مطلوبة.',
            'times.array' => 'الأوقات يجب أن تكون مصفوفة.',
            'times.*.regex' => 'تنسيق الوقت غير صحيح. استخدم الصيغة HH:MM.',
            'stopPoints.array' => 'نقاط التوقف يجب أن تكون مصفوفة.',
            'stopPoints.*.stopName.required_with' => 'اسم نقطة التوقف مطلوب.',
            'stopPoints.*.stopName.string' => 'اسم نقطة التوقف يجب أن يكون نصاً.',
            'stopPoints.*.stopName.max' => 'اسم نقطة التوقف يجب ألا يتجاوز 255 حرفاً.',
            'stopPoints.*.arrivalTime.required_with' => 'وقت الوصول لنقطة التوقف مطلوب.',
            'stopPoints.*.arrivalTime.regex' => 'تنسيق وقت الوصول غير صحيح. استخدم الصيغة HH:MM.',
            'finalArrivalTime.regex' => 'تنسيق وقت الوصول النهائي غير صحيح. استخدم الصيغة HH:MM.',
            'isActive.boolean' => 'حالة التفعيل يجب أن تكون صحيحة أو خاطئة.',
            'notes.string' => 'الملاحظات يجب أن تكون نصاً.',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',
        ];
    }
}
