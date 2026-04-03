<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class EstimateVoucherKeywordSearch implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        if (!is_string($value) || $value === '') return;

        $keywords = array_filter(explode(' ', str_replace('　', ' ', $value)));

        foreach ($keywords as $keyword) {
            if (str_starts_with($keyword, '-')) { // -から始まるキーワードが含まれる場合
                $excludeKeyword = mb_substr($keyword, 1); // キーワードから初めの1文字(-)を削除
                
                $query->whereNot(function ($q) use ($excludeKeyword) {
                    $q->whereAny([
                        'date',
                        'expiry_date',
                        'customer_note',
                        'internal_note',
                        'user_name',
                        'client_name',
                        'total_net_amount',
                        'total_tax_amount',
                        'total_gross_amount'
                    ], 'LIKE', '%' . $excludeKeyword . '%')
                    ->orWhereHas('bodies', function ($subQ) use ($excludeKeyword) {
                        $subQ->whereAny([
                            'content',
                            'unit_price',
                            'customer_note',
                            'internal_note'
                        ], 'LIKE', '%' . $excludeKeyword . '%');
                    })
                    ->orWhereHas('footers', function ($subQ) use ($excludeKeyword) {
                        $subQ->whereAny([
                            'total_net_amount',
                            'total_tax_amount',
                            'total_gross_amount'
                        ], 'LIKE', '%' . $excludeKeyword . '%');
                    })
                    ->orWhereHas('client', function ($subQ) use ($excludeKeyword) {
                        $subQ->whereAny([
                            'code',
                            'name',
                            'postal',
                            'address',
                            'tel',
                            'fax',
                            'email',
                            'website'
                        ], 'LIKE', '%' . $excludeKeyword . '%');
                    })
                    ->orWhereHas('user', function ($subQ) use ($excludeKeyword) {
                        $subQ->whereAny([
                            'code',
                            'name',
                            'email'
                        ], 'LIKE', '%' . $excludeKeyword . '%');
                    });
                });
            } else {
                $query->where(function ($q) use ($keyword) {
                    $q->whereAny([
                        'date',
                        'expiry_date',
                        'customer_note',
                        'internal_note',
                        'user_name',
                        'client_name',
                        'total_net_amount',
                        'total_tax_amount',
                        'total_gross_amount'
                    ], 'LIKE', '%' . $keyword . '%')
                    ->orWhereHas('bodies', function ($subQ) use ($keyword) {
                        $subQ->whereAny([
                            'content',
                            'unit_price',
                            'customer_note',
                            'internal_note'
                        ], 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhereHas('footers', function ($subQ) use ($keyword) {
                        $subQ->whereAny([
                            'total_net_amount',
                            'total_tax_amount',
                            'total_gross_amount'
                        ], 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhereHas('client', function ($subQ) use ($keyword) {
                        $subQ->whereAny([
                            'code',
                            'name',
                            'postal',
                            'address',
                            'tel',
                            'fax',
                            'email',
                            'website'
                        ], 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhereHas('user', function ($subQ) use ($keyword) {
                        $subQ->whereAny([
                            'code',
                            'name',
                            'email'
                        ], 'LIKE', '%' . $keyword . '%');
                    });
                });
            }
        }
    }
}