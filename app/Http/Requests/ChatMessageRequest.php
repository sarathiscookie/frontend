<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
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
            "message"    => "required|string|max:350",
            "message.*"  => "required|string|max:350",
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'message.required'  => __('inquiry.messageRequired'),
            'message.*.required'  => __('inquiry.messageRequired'),
            'message.max'    => __('inquiry.messageMax'),
            'message.*.max'    => __('inquiry.messageMax')
        ];
    }
}
