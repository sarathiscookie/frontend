<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserProfileRequest extends FormRequest
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
            'firstName' => 'required|regex:/^[\pL\pM\pN\s]+$/u|max:255',
            'lastName'  => 'required|regex:/^[\pL\pM\pN\s]+$/u|max:255|min:2',
            'street'    => 'required|regex:/^[\pL\pM\pN\s ,. -äöüÄÖÜß]+$/|max:255',
            'city'      => 'required|regex:/^[\pL\pM\pN\s-]+$/|max:255',
            'country'   => 'required|regex:/^[\pL\pM\pN\s]+$/|not_in:0',
            'zipcode'   => 'required|regex:/^[A-Z0-9 -]{3,9}$/',
            'mobile'    => 'max:20',
            'phone'     => 'max:20'
        ];
    }
}
