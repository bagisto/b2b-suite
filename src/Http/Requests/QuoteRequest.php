<?php

namespace Webkul\B2BSuite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
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
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'max:1000'],
            'status'        => ['sometimes', 'required', 'in:draft,open,submitted,negotiation,ordered,cancelled,rejected,closed'],
            'cart_id'       => ['required'],
            'attachments'   => ['sometimes', 'array'],
            'attachments.*' => ['mimes:bmp,jpeg,jpg,png,webp'],
        ];
    }
}
