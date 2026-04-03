<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ClientKeywordSearch implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        if (!is_string($value) || $value === '') return;

        $keywords = array_filter(explode(' ', str_replace('　', ' ', $value)));

        foreach ($keywords as $keyword) {
            if (str_starts_with($keyword, '-')) { // -から始まるキーワードが含まれる場合
                $excludeKeyword = mb_substr($keyword, 1); // キーワードから初めの1文字(-)を削除
                
                $query->whereNone([ // 除外検索
                    'code',
                    'name',
                    'postal',
                    'address',
                    'tel',
                    'fax',
                    'email',
                    'website'
                ], 'LIKE', '%' . $excludeKeyword . '%');
            } else {
                $query->whereAny([ // 検索
                    'code',
                    'name',
                    'postal',
                    'address',
                    'tel',
                    'fax',
                    'email',
                    'website'
                ], 'LIKE', '%' . $keyword . '%');
            }
        }
    }
}