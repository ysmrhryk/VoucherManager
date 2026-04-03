<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\{ User, Client, EstimateVoucherRowType, TaxRate, NameSuffix, EstimateVoucherBody };
use Illuminate\Validation\Rule;

class EstimateVoucherRequest extends FormRequest
{
    const MITSUMORI = 1;
    const NEBIKI = 2;
    const MEMO = 3;
    const YOHAKU = 4;

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
            'expiry_date' => 'required|date|gte:date', 
            'customer_note' => 'nullable|string', 
            'internal_note' => 'nullable|string', 
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')], 
            'client_id' => ['required', 'integer', Rule::exists(Client::class, 'id')], 
            'extension_client_name' => 'nullable|string|max:255',
            'name_suffix_id' => [
                'required_with:extension_client_name', // extensionが存在するなら
                'prohibited_if:extension_client_name,', // extensionが存在しないなら
                'nullable', 
                'integer', 
                Rule::exists(NameSuffix::class, 'id')
            ], 
            
            'bodies' => 'required|array|between:1,999',
            'bodies.*.id' => ['nullable', Rule::exists(EstimateVoucherBody::class, 'id')],
            'bodies.*.estimate_voucher_row_type_id' => ['required', 'integer', Rule::exists(EstimateVoucherRowType::class, 'id')],
            'bodies.*.line_number' => 'required|integer|distinct|between:1,999',

            // 以下の4ルールはrow_typeの値によってルールが変更される
            'bodies.*.quantity' => Rule::forEach(function ($value, string $attribute) {
                $row_type_id = $this->get_row_type_id($attribute);

                if(in_array($row_type_id, [self::MITSUMORI, self::NEBIKI], true)) return ['required', 'integer', 'gt:0'];
                else if(in_array($row_type_id, [self::MEMO, self::YOHAKU], true)) return ['nullable', 'prohibited'];
            }),
            'bodies.*.unit_price' => Rule::forEach(function ($value, string $attribute) {
                $row_type_id = $this->get_row_type_id($attribute);

                if($row_type_id == self::MITSUMORI) return ['required', 'integer', 'gt:0'];
                else if($row_type_id == self::NEBIKI) return ['required', 'integer', 'lt:0'];
                else if(in_array($row_type_id, [self::MEMO, self::YOHAKU], true)) return ['nullable', 'prohibited'];
            }),
            'bodies.*.tax_rate_id' => Rule::forEach(function ($value, string $attribute) {
                $row_type_id = $this->get_row_type_id($attribute);

                if(in_array($row_type_id, [self::MITSUMORI, self::NEBIKI], true)) return ['required', 'integer', Rule::exists(TaxRate::class, 'id')];
                else if(in_array($row_type_id, [self::MEMO, self::YOHAKU], true)) return ['nullable', 'prohibited'];
            }),
            'bodies.*.content' => Rule::forEach(function ($value, string $attribute) {
                $row_type_id = $this->get_row_type_id($attribute);

                if(in_array($row_type_id, [self::MITSUMORI, self::NEBIKI, self::MEMO], true)) return ['required', 'string', 'max:255'];
                else if($row_type_id == self::YOHAKU) return ['nullable', 'prohibited'];
            }),
        ];
    }

    private function get_row_type_id(string $attribute): int
    {
        return (int) $this->input('bodies.'.explode('.', $attribute)[1].'.estimate_voucher_row_type_id');
    }
}
