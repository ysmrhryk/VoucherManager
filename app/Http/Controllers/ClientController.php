<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Http\Requests\ClientRequest;
use Spatie\QueryBuilder\{ QueryBuilder, AllowedFilter };
use Spatie\QueryBuilder\Enums\FilterOperator;
use App\Filters\ClientKeywordSearch;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = QueryBuilder::for(Client::class)
        ->allowedFilters([
            AllowedFilter::custom('keywords', new ClientKeywordSearch()), // キーワード検索
            AllowedFilter::exact('code'), // unique制約つけてあるので
            AllowedFilter::exact('billing_cycle_type_id'), // id 完全一致
            AllowedFilter::exact('billing_day'), // integer 完全一致
            AllowedFilter::exact('payment_method_id'), // id 完全一致
            AllowedFilter::exact('transaction_type_id'), // id 完全一致
            AllowedFilter::exact('name_suffix_id'), // id 完全一致
            AllowedFilter::exact('user_id'), // id 完全一致
            AllowedFilter::exact('allow_login') // boolean 完全一致
        ])
        ->defaultSort('code') // code順でしか出力されない仕様
        ->with('transaction_type', 'billing_cycle_type', 'payment_method', 'name_suffix', 'user')
        ->paginate(100); // 一ページ当たり100件

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request)
    {
        $client = Client::create($request->validated())->fresh();

        return response()->json($client, 201); // 201 作成したデータを返す
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return response()->json($client->fresh([
            'transaction_type',
            'billing_cycle_type',
            'payment_method',
            'name_suffix',
            'user'
        ])); // 200
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return response()->json($client->fresh()); // 200 編集した新しいデータを返す（データベースの今の状態を返す
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->noContent(); // 204
        // もしかしたら削除したデータに関して何か返したほうが、フロントが楽になるかもしれない
    }
}
