<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'game_id' => 'required|exists:games,id',
            'denomination_id' => 'required|exists:denominations,id',
            'account_id' => 'required|string|max:100',
            'server_id' => 'nullable|string|max:50',
            'username' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:20',
            'quantity' => 'nullable|integer|min:1|max:10',
            'promo_code' => 'nullable|string|max:50',
            'payment_method' => 'required|in:QRIS,VA,EWALLET',
            'payment_channel' => 'nullable|string|max:50',
            'g-recaptcha-response' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => 'User ID wajib diisi',
            'email.required' => 'Email wajib diisi',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi',
            'payment_method.required' => 'Metode pembayaran wajib dipilih',
            'g-recaptcha-response.required' => 'Verifikasi reCAPTCHA diperlukan',
        ];
    }
}