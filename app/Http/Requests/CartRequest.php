<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
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
            'guest'   => 'array',
            'street'  => 'required|regex:/^[\pL\pM\pN\s]+$/u|max:255',
            'city'    => 'required|regex:/^[\pL\pM\pN\s]+$/u|max:255',
            'country' => 'required|regex:/^[\pL\pM\pN\s]+$/u|not_in:0',
            'zipcode' => 'required|regex:/^[0-9]{5}(\-[0-9]{4})?$/',
            'mobile'  => 'max:20',
            'phone'   => 'max:20'
        ];

        foreach($this->request->get('guest') as $key => $val){
            if($this->request->get('guest')[$key]['sleeping_place'] === '1') {
                $rules['guest.'.$key.'.sleeps'] = 'required|not_in:0';
                $rules['guest.'.$key.'.comments'] = 'max:300';
            }
            else {
                $rules['guest.'.$key.'.comments'] = 'max:300';
                $rules['guest.'.$key.'.beds'] = 'required_without:guest.'.$key.'.dormitory';
                $rules['guest.'.$key.'.dormitory'] = 'required_without:guest.'.$key.'.beds';
            }
        }

        return $rules;
    }
}
