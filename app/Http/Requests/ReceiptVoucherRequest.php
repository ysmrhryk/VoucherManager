<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\{ User, Client, PaymentMethod, NameSuffix, ReceiptVoucherBody };
use Illuminate\Validation\Rule;
use App\Rules\AfterLatestInvoiceDate;

class ReceiptVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', new AfterLatestInvoiceDate($this?->client_id)], 
            'internal_note' => 'nullable|string', 
            'client_id' => ['required', 'integer', Rule::exists(Client::class, 'id')], 
            
            'bodies' => 'required|array|between:1,999',
            'bodies.*.id' => ['nullable', Rule::exists(ReceiptVoucherBody::class, 'id')],
            'bodies.*.payment_method_id' => ['required', 'integer', Rule::exists(PaymentMethod::class, 'id')],
            'bodies.*.line_number' => 'required|integer|distinct|between:1,999',
            'bodies.*.amount' => 'required|integer|gt:0', // 返金などは別画面で処理
            'bodies.*.content' => 'nullable|string|max:255',
        ];
    }
}
