<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class RequestLogKeywordSearch implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        if (!is_string($value) || $value === '') return;

        $keywords = array_filter(explode(' ', str_replace('　', ' ', $value)));

        foreach ($keywords as $keyword) {
            if (str_starts_with($keyword, '-')) { // -から始まるキーワードが含まれる場合
                $excludeKeyword = mb_substr($keyword, 1); // キーワードから初めの1文字(-)を削除
              
                // SQLとLaravelのクエリビルダに関する理解が足りませんでした！！AIに頼らずちゃんと勉強しましょう。
                $query->whereNot(function ($q) use ($excludeKeyword) {
                    $q->whereAny([ // 検索
                        'method',
                        'path', 
                        'payload', 
                        'ip_address'
                    ], 'LIKE', '%' . $excludeKeyword . '%')
                    ->orWhereHas('user', function ($subQ) use ($excludeKeyword) {
                        $subQ->whereAny([
                            'code',
                            'name'
                        ], 'LIKE', '%' . $excludeKeyword . '%');
                    });
                });
            } else {
                $query->where(function ($q) use ($keyword) {
                    $q->whereAny([ // 検索
                        'method',
                        'path', 
                        'payload', 
                        'ip_address'
                    ], 'LIKE', '%' . $keyword . '%')
                    ->orWhereHas('user', function ($subQ) use ($keyword) {
                        $subQ->whereAny([
                            'code',
                            'name'
                        ], 'LIKE', '%' . $keyword . '%');
                    });
                });
            }
        }
    }
}