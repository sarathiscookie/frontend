<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
        $rules = [
            'dateFrom' => 'required',
            'dateTo'   => 'required',
        ];

        if($this->request->get('sleeping_place') === '1') {
            $rules['sleeps'] = 'required|not_in:0';
        }
        else {
            $rules['beds']   = 'required_without:dorms';
            $rules['dorms']  = 'required_without:beds';
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
            'dateFrom.required'      => __('searchDetails.dateFromRequired'),
            'dateTo.required'        => __('searchDetails.dateToRequired'),
            'beds.required_without'  => __('searchDetails.bedsRequired_without'),
            'dorms.required_without' => __('searchDetails.dormsRequired_without'),
            'sleeps.required'        => __('searchDetails.sleepsRequired')
        ];
    }
}
