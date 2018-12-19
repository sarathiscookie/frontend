<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquiryRequest extends FormRequest
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
        $rules =  [
            'street'  => 'required|regex:/^[\pL\pM\pN\s ,. -äöüÄÖÜß]+$/|max:255',
            'city'    => 'required|regex:/^[\pL\pM\pN\s]+$/|max:255',
            'country' => 'required|regex:/^[\pL\pM\pN\s]+$/|not_in:0',
            'zipcode' => 'required|regex:/^[A-Z0-9 -]{3,9}$/',
            'mobile'        => 'max:20',
            'phone'         => 'max:20',
            'comments'      => 'max:300'
        ];
        if(session()->get('sleeping_place') === 1) {
            $rules['sleeps']    = 'required|not_in:0';
        }
        else {
            $rules['beds']      = 'required_without:dormitory';
            $rules['dormitory'] = 'required_without:beds';
        }
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'beds.required_without'      => __('searchDetails.bedsRequired_without'),
            'dormitory.required_without' => __('searchDetails.dormsRequired_without'),
            'sleeps.required'            => __('searchDetails.sleepsRequired')
        ];
    }
}
