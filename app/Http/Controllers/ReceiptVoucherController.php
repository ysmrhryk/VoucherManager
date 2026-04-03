<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ ReceiptVoucher, PaymentMethod };
use App\Http\Requests\ReceiptVoucherRequest;
use Spatie\QueryBuilder\{ QueryBuilder, AllowedFilter };
use Spatie\QueryBuilder\Enums\FilterOperator;
use App\Filters\ReceiptVoucherKeywordSearch;
use TCPDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ReceiptVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = QueryBuilder::for(ReceiptVoucher::class)
        ->allowedFilters([
            AllowedFilter::custom('keywords', new ReceiptVoucherKeywordSearch()), // キーワード検索

            AllowedFilter::operator('date_from', FilterOperator::GREATER_THAN_OR_EQUAL, 'date'), // date 範囲
            AllowedFilter::operator('date_to', FilterOperator::LESS_THAN_OR_EQUAL, 'date'), // date 範囲

            AllowedFilter::exact('invoice_id'), // id 完全一致
            AllowedFilter::exact('user_id'), // id 完全一致
            AllowedFilter::exact('client_id'), // id 完全一致
            AllowedFilter::exact('name_suffix_id'), // id 完全一致
        ])
        ->defaultSort('id')
        ->with('bodies', 'client', 'client.name_suffix')
        ->allowedSorts(['id', 'date', 'created_at', 'updated_at'])
        ->paginate(100); // 一ページ当たり100件

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReceiptVoucherRequest $request)
    {
        $receipt_voucher = DB::transaction(function () use ($request) {
            $bodies = $this->build_bodies($request->validated());

            $voucher = ReceiptVoucher::create([
                ...$request->validated(), 
                'total_amount' => $bodies->sum('amount'),
            ]);

            $voucher->bodies()->createMany($bodies);

            return $voucher;
        });

        return response()->json($receipt_voucher, 201); // 201 作成したデータを返す
    }

    /**
     * Display the specified resource.
     */
    public function show(ReceiptVoucher $receipt_voucher)
    {
        return response()->json($receipt_voucher->fresh([
            'bodies', 
            'client', 
            'client.name_suffix',
            'client.transaction_type',
            'client.billing_cycle_type',
            'client.payment_method',
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReceiptVoucherRequest $request, ReceiptVoucher $receipt_voucher)
    {
        DB::transaction(function () use ($request, $receipt_voucher) {
            $receipt_voucher = ReceiptVoucher::lockForUpdate()->findOrFail($receipt_voucher->id);

            $bodies = $this->build_bodies($request->validated());

            $receipt_voucher->update([
                ...$request->validated(), 
                'total_amount' => $bodies->sum('amount'),
            ]); // ヘッダをアップデート

            // ない行を削除
            $receipt_voucher->bodies()->whereNotIn('id', $bodies->pluck('id')->filter())->delete();

            // 更新 or 登録
            $bodies->each(fn ($body) => $receipt_voucher->bodies()->updateOrCreate(['id' => $body['id'] ?? null], $body));
        });

        return response()->json($receipt_voucher->fresh()); // 200 編集した新しいデータを返す（データベースの今の状態を返す
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReceiptVoucher $receipt_voucher)
    {
        $receipt_voucher->delete();

        return response()->noContent(); // 204
        // もしかしたら削除したデータに関して何か返したほうが、フロントが楽になるかもしれない
    }

    public function pdf()
    {
        //
    }

    private function build_bodies(array $validated): Collection
    {
        $bodies = collect($validated['bodies']);

        $payment_methods = PaymentMethod::whereIn('id', $bodies->pluck('payment_method_id')->filter())->get()->keyBy('id');

        $bodies = $bodies->map(function ($body) use ($payment_methods) {
            $body['payment_method_value'] = $payment_methods->get($body['payment_method_id'], null)?->value;

            return $body;
        });

        return $bodies;
    }
}
