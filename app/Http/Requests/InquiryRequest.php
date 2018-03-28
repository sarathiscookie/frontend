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
            /*'street'        => 'required|max:255',
            'city'          => 'required|max:255',
            'country'       => 'required|not_in:0',
            'zipcode'       => 'required|max:25',
            'mobile'        => 'required|max:20',
            'phone'         => 'required|max:20',
            'comments'      => 'max:300'*/
        ];

        // write rules for beds dorms sleeps


        /*if(session('sleeping_place') != 1)
        {
            $this->validate($request, [
                'dateFrom' => 'required',
                'dateTo' => 'required',
                'beds' => 'required_without:dorms',
                'dorms' => 'required_without:beds',
            ]);
        }
        else {
            $this->validate($request, [
                'dateFrom' => 'required',
                'dateTo' => 'required',
                'sleeps' => 'required|not_in:0',
            ]);
        }*/

        return $rules;
    }
}
