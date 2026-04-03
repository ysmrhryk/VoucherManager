<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReceiptVoucherBody extends Model
{
    protected $fillable = [
        'payment_method_id',
        'payment_method_value',
        'line_number',
        'amount',
        'content',
    ];

    public function header()
    {
        return $this->belongsTo(ReceiptVoucher::class, 'receipt_voucher_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('order_by_line_number_asc', function (Builder $builder) {
            $builder->orderBy('line_number', 'asc');
        });
    }
}
