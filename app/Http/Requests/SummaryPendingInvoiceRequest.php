<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\{ BillingCycleType, PaymentMethod, TransactionType, User };

class SummaryPendingInvoiceRequest extends FormRequest
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
            'date' => 'required|date',

            'billing_cycle_type_id' => ['nullable', Rule::exists(BillingCycleType::class, 'id')],
            'billing_day' => [
                'nullable', 'integer', 'between:1,31',
                'prohibited_unless:billing_cycle_type_id,2',
            ],

            'payment_method_id' => ['nullable', Rule::exists(PaymentMethod::class, 'id')],
            'transaction_type_id' => ['nullable', Rule::exists(TransactionType::class, 'id')],
            'user_id' => ['nullable', Rule::exists(User::class, 'id')],
            
            'allow_login' => 'nullable|boolean',

            'ignore_if_empty' => 'nullable|boolean'
        ];
    }
}
