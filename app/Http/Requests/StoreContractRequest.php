<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fromDate' => 'required',
            'toDate' => 'required',
            'cardNumber' => 'min:16',
        ];
    }

    public function messages(): array
    {
        return [
            'fromDate.required' => 'تاریخ شروع اجباری است',
            'toDate.required' => 'تاریخ پایان اجباری است',
            'cardNumber.min' => 'تعداد کاراکتر شماره کارت حداقل 16 رقم است',
        ];
    }
}
