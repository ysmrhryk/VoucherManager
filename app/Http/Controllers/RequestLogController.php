<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestLog;
use App\Filters\RequestLogKeywordSearch;
use Spatie\QueryBuilder\{ QueryBuilder, AllowedFilter };
use Spatie\QueryBuilder\Enums\FilterOperator;

class RequestLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = QueryBuilder::for(RequestLog::class)
        ->allowedFilters([
            AllowedFilter::custom('keywords', new RequestLogKeywordSearch()), // キーワード検索
            AllowedFilter::exact('user_id'), // id 完全一致
            AllowedFilter::exact('method'), // 完全一致でいいと思う
            'path', // ルートの数選択肢用意するのめんどくさいので
            'ip_address',
            AllowedFilter::operator('created_at_from', FilterOperator::GREATER_THAN_OR_EQUAL, 'created_at'), // 「いつからいつまでのログ」で絞り込めるようにする
            AllowedFilter::operator('created_at_to', FilterOperator::LESS_THAN_OR_EQUAL, 'created_at')
        ])
        ->defaultSort('-created_at')
        ->paginate(100); // 一ページ当たり100件

        return response()->json($data);
    }
}
