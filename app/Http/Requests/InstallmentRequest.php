<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallmentRequest extends FormRequest
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
            'payment_type' => 'required|integer|exists:payment_types,id', // Payment type ID must exist in payment_types
            'name' => 'required|string|max:255',
            'personal_number' => 'required|string|max:50',
            'phone_no' => 'required|string|max:50',
            'address' => 'required|max:255',
            'city_id' => 'required|integer', // Ensure city_id is an integer
            'product_ids' => 'required|array', // Expecting product_ids to be an array
        ];
    }
    public function messages()
    {
        return [
            'payment_type.required' => __('validation.payment_type_required'),
            'address.required' => __('validation.address_required'),
            'name.required' => __('validation.address_name_required'),
            'personal_number.required' => __('validation.address_personal_number_required'),
            'phone_no.required' => __('validation.address_phone_no_required'),
            // 'address.address.required' => __('validation.address_address_required'),
            'city_id.required' => __('validation.address_city_id_required'),
            'product_ids.required' => __('validation.product_ids_required'),
            'product_ids.*.exists' => __('validation.product_id_exists'),
        ];
    }

}
