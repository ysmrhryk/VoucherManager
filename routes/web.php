<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Models\{ NameSuffix, TransactionType, BillingCycleType, TaxRate, ServiceVoucherRowType, EstimateVoucherRowType };

Route::prefix('api')->group(function () { 
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () { // ログイン済みユーザーのみアクセスできる
    Route::prefix('pdf')->group(function () { // PDFにて何かしら発行する場合
        // 見積書を印刷します。一枚づつしか印刷できないようにしてしまって、
        // Route::post('estimate-vouchers', [EstimateVoucherController::class, 'pdf']); // 未実装
        Route::get('estimate-vouchers/{estimate_voucher}', [EstimateVoucherController::class, 'pdf']); // 単一伝票

        // 領収書(受領書？)を印刷します (memo: 出金のほうはpdf出力が多分不要)
        // Route::post('receipt-vouchers', [ReceiptVoucherController::class, 'pdf']); // 未実装
        // Route::get('receipt-vouchers/{receipt_vouchers}', [ReceiptVoucherController::class, 'pdf']); // 未実装-単一伝票

        // 伝票を印刷します
        // Route::post('service-vouchers', [ServiceVoucherController::class, 'pdf']); // 未実装
        Route::get('service-vouchers/{service_voucher}', [ServiceVoucherController::class, 'pdf']); // 単一伝票

        // 請求予定額の一覧を表示します
        Route::get('pending-invoices/{uuid}', [PendingInvoiceController::class, 'pdf']); // 未実装

        // 請求額一覧または請求書を印刷します
        Route::get('issued-invoices/{invoice}', [IssuedInvoiceController::class, 'pdf']); // 未実装
    });

    Route::prefix('api')->group(function () { 
        Route::get('user', [AuthController::class, 'user']);

        // 担当者のパスワードリセット
        Route::put('users/{user}/update-password', [UserController::class, 'updatePassword']); // 実装済み

        Route::apiResources([
            // 得意先
            'clients' => ClientController::class, // 実装済み

            // 担当者
            'users' => UserController::class, // 実装済み

            // 見積書
            'estimate-vouchers' => EstimateVoucherController::class, // ほぼ実装済み(計算周りがまだ)

            // 入金
            'receipt-vouchers' => ReceiptVoucherController::class, // ほぼ実装済み(計算周りがまだ)

            // 返金
            'refund-vouchers' => RefundVoucherController::class, // ほぼ実装済み(計算周りがまだ)

            // 売上
            'service-vouchers' => ServiceVoucherController::class, // ほぼ実装済み(計算周りがまだ)
        ]);

        // 支払方法
        Route::post('payment-methods/reorder', [PaymentMethodController::class, 'reorder']);
        Route::apiResource('payment-methods', PaymentMethodController::class)->only(['index', 'store', 'destroy']);

        // Httpリクエストのログを表示します
        Route::apiResource('request-logs', RequestLogController::class)->only(['index']); // 実装済み
        
        Route::prefix('pending-invoices')->group(function () {
            // 未請求リストがpostなのはFormRequestを通すため
            // FormRequestを採用したのはSpatieのクエリビルダでleftJoinみたいな複雑なことをするのが難しそうだったので
            // 「単純に情報を取得するだけじゃなくて計算処理が入るから」ということで納得しておきたい

            Route::post('summary', [PendingInvoiceController::class, 'summary']); // 請求予定額の一覧を表示します
            Route::post('issue', [PendingInvoiceController::class, 'issue']); // 請求書を作成します
            Route::post('request-pdf', [PendingInvoiceController::class, 'request_pdf']); // 請求予定額リストのパラメーターだけ先に送るため
        });

        // 請求書の一覧を表示します
        Route::get('issued-invoices', [IssuedInvoiceController::class, 'index']); // 未実装

        // 請求書を削除します
        Route::delete('issued-invoices/{invoice}', [IssuedInvoiceController::class, 'destroy']); // 未実装
        
        // 自社情報などの設定をします
        Route::get('settings', [SettingsController::class, 'show']); // 実装済み
        Route::put('settings', [SettingsController::class, 'update']); // 実装済み

        // selectメニューなどで使用する
        Route::get('transaction-types', fn() => TransactionType::all());
        Route::get('name-suffixes', fn() => NameSuffix::all());
        Route::get('billing-cycle-types', fn() => BillingCycleType::all());
        Route::get('tax-rates', fn() => TaxRate::all());
        Route::get('service-voucher-row-type', fn() => ServiceVoucherRowType::all());
        Route::get('estimate-voucher-row-type', fn() => EstimateVoucherRowType::all());
    });
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');