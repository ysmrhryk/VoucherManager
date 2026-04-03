<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Http\Requests\{ StorePaymentMethodRequest, ReorderPaymentMethodRequest };
use Spatie\QueryBuilder\{ QueryBuilder, AllowedFilter };
use App\Filters\PaymentMethodKeywordSearch;
use Illuminate\Support\Arr;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(PaymentMethod::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentMethodRequest $request)
    {
        $payment_method = PaymentMethod::create($request->validated())->fresh();

        return response()->json($payment_method, 201); // 201 作成したデータを返す
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $payment_method)
    {
        return response()->json($payment_method); // 200
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $payment_method)
    {
        $payment_method->delete();

        return response()->noContent(); // 204
        // もしかしたら削除したデータに関して何か返したほうが、フロントが楽になるかもしれない
    }

    public function reorder(ReorderPaymentMethodRequest $request)
    {
        // Valueに値を置かないとInsert文でエラーが出る。
        // バリデーションで存在するIDしか来ないことを確認はしてある
        // 仮にundefinedで新しいデータができてもそんなに困らない
        $upsert = Arr::map($request->validated()['ids'], function ($id, $index) {
            return ['id' => $id, 'value' => 'undefined', 'sort_id' => $index + 1];
        });

        logger($upsert);

        PaymentMethod::upsert($upsert, ['id'], ['sort_id']);

        return response()->json(PaymentMethod::all());
    }
}
