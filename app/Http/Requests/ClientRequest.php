<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\{ Client, BillingCycleType, PaymentMethod, TransactionType, NameSuffix, User };

class ClientRequest extends FormRequest
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
            'code' => ['required', 'regex:/^[a-z0-9-_.]*$/i', 'max:255', Rule::unique(Client::class, 'code')->ignore($this->client)], // 必須項目
            'name' => 'required|string|max:255', // 必須項目
            
            'postal' => 'nullable|string|regex:/^\d{3}-?\d{4}$/|max:8', // 日本の郵便番号でのみ動く
            'address' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:64',
            'fax' => 'nullable|string|max:64',
            'email' => ['nullable', 'email', 'max:255', 'required_if:allow_login,1', Rule::unique(Client::class, 'email')->ignore($this->client)],
            'website' => 'nullable|string|max:255',
            
            'initial_previous_invoice_amount' => 'nullable|integer',

            'billing_cycle_type_id' => ['nullable', 'integer', Rule::exists(BillingCycleType::class, 'id')],
            'billing_day' => [
                'nullable',
                'required_if:billing_cycle_type_id,2', // 日付指定の場合はrequired
                'prohibited_unless:billing_cycle_type_id,2', // 日付指定以外の場合はprohibited
                'integer', 
                'between:1,31'
            ],
            'payment_method_id' => ['nullable', 'integer', Rule::exists(PaymentMethod::class, 'id')],
            'transaction_type_id' => ['nullable', 'integer', Rule::exists(TransactionType::class, 'id')],
            'name_suffix_id' => ['required', 'integer', Rule::exists(NameSuffix::class, 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            
            'allow_login' => 'required|boolean'
        ];
    }
}
