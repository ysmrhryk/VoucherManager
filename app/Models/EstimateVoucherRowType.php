<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EstimateVoucherRowType extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('order_by_sort_id_asc', function (Builder $builder) {
            $builder->orderBy('sort_id', 'asc');
        });
    }
}
