<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyPromoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'promo_code' => 'required|string|max:50',
            'denomination_id' => 'required|exists:denominations,id',
            'quantity' => 'nullable|integer|min:1|max:10',
        ];
    }
}