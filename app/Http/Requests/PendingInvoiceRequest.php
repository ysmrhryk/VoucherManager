<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Client;
use Illuminate\Validation\Rule;
use App\Models\Invoice;
use Closure;

class PendingInvoiceRequest extends FormRequest
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
        $invalid_client_ids = Invoice::whereIn('client_id', $this->client_ids)
        ->where('date', '>=', $this->date)
        ->pluck('client_id')
        ->toArray();

        return [
            'date' => 'required|date',
            'client_ids' => ['required', 'array', 'min:1'],
            'client_ids.*' => [
                'required', 
                'distinct', 
                Rule::exists(Client::class, 'id'),
                function (string $attribute, mixed $value, Closure $fail) use ($invalid_client_ids) {
                    if(in_array($value, $invalid_client_ids, true)){
                        $fail("The {$attribute} is invalid.");
                    }
                }
            ]
        ];
    }
}
