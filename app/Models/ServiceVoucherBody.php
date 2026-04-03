<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ServiceVoucherBody extends Model
{
    protected $fillable = [
        'service_voucher_row_type_id',
        'line_number',
        'quantity',
        'unit_price',
        'content',
        'tax_rate_id',
        'tax_rate_name',
        'tax_rate_rate',
        'tax_rate_mark',
    ];

    public function header()
    {
        return $this->belongsTo(ServiceVoucher::class, 'service_voucher_id');
    }

    public function row_type()
    {
        return $this->belongsTo(ServiceVoucherRowType::class, 'service_voucher_row_type_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('order_by_line_number_asc', function (Builder $builder) {
            $builder->orderBy('line_number', 'asc');
        });
    }
}
