<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EstimateVoucherFooter extends Model
{
    protected $fillable = [
        'estimate_voucher_id',
        'tax_rate_id',
        'tax_rate_rate',
        'tax_rate_name',

        'total_net_amount',
    ];

    public function header()
    {
        return $this->belongsTo(EstimateVoucher::class, 'estimate_voucher_id');
    }

    public function tax_rate()
    {
        return $this->belongsTo(TaxRate::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('order_by_tax_rate_id_asc', function (Builder $builder) {
            $builder->orderBy('tax_rate_id', 'asc');
        });
    }
}
