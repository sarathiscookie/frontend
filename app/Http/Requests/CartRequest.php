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
        return [
            'guest.*.sleeps'     => 'required|not_in:0',
            'guest.*.beds'       => 'required_without:guest.*.dormitory',
            'guest.*.dormitory'  => 'required_without:guest.*.beds',
            'guest.*.comments'   => 'max:300',
        ];
    }
}
