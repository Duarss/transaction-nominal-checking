<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'doc_id' => ['required', 'string', 'exists:transactions,doc_id'],
            'actual_nominal' => ['required', 'string', 'min:0'],
            'is_approved' => ['sometimes', 'boolean'],
            'is_rechecked' => ['sometimes', 'boolean'],
        ];
    }
}
