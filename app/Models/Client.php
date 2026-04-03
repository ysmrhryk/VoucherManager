<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Client extends Model
{
    protected $fillable = [
        'code',
        'name',
        'postal',
        'address',
        'tel',
        'fax',
        'email',
        'website',
        'initial_previous_invoice_amount',
        'billing_cycle_type_id',
        'billing_day',
        'payment_method_id',
        'transaction_type_id',
        'name_suffix_id',
        'allow_login',
        'user_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'code' => null,
            'name' => null,
        ]);
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class)->withDefault([
            'id' => null,
            'value' => null
        ]);
    }

    public function billing_cycle_type()
    {
        return $this->belongsTo(BillingCycleType::class)->withDefault([
            'id' => null,
            'value' => null
        ]);
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class)->withDefault([
            'id' => null,
            'code' => null,
            'value' => null
        ]);
    }

    public function name_suffix()
    {
        return $this->belongsTo(NameSuffix::class)->withDefault([
            'id' => null,
            'value' => null
        ]);
    }

    protected function casts(): array
    {
        return [
            'allow_login' => 'boolean',
        ];
    }

    public function latestInvoice()
    {
        // そんなメソッドがあるなんで知らなかったんだけど↓！！！
        return $this->hasOne(Invoice::class)->latestOfMany('date');
    }
}
