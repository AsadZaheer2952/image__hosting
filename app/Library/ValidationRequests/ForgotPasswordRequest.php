<?php namespace Image\ValidationRequests;


use Image\Abstracts\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required',
        ];

    }
}
