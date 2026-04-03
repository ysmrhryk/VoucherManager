<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\{ PendingInvoiceRequest, SummaryPendingInvoiceRequest };
use Spatie\QueryBuilder\{ QueryBuilder, AllowedFilter };
use Spatie\QueryBuilder\Enums\FilterOperator;
use App\Models\{ 
    Client, Invoice, Settings, TaxRate, NameSuffix, 
    ServiceVoucher, ServiceVoucherBody, ServiceVoucherFooter, 
    ReceiptVoucher, ReceiptVoucherBody, 
    RefundVoucher, RefundVoucherBody
};
use Illuminate\Support\Facades\{ DB, Cache, Storage, Log };
use Illuminate\Support\{ Str, Arr };
use TCPDF;
use TCPDF_FONTS;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;

class PendingInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function summary(SummaryPendingInvoiceRequest $request)
    {
        $validated = $request->validated();
        
        $query = Client::query()
        ->select(
            'clients.id AS client_id',
            'clients.name AS client_name',
            'clients.code AS client_code',
            DB::raw('previous_invoices.date as date'),
            DB::raw('IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount) AS previous_invoice_amount'),
            DB::raw('IFNULL(receipt_vouchers.amount, 0) AS total_receipt_amount'),
            DB::raw('IFNULL(refund_vouchers.amount, 0) AS total_refund_amount'),
            DB::raw('(IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount) - IFNULL(receipt_vouchers.amount, 0) + IFNULL(refund_vouchers.amount, 0)) AS carried_forward_amount'),
            DB::raw('IFNULL(service_vouchers.total_net_amount, 0) AS total_net_amount'),
            DB::raw('IFNULL(service_vouchers.total_tax_amount, 0) AS total_tax_amount'),
            DB::raw('(IFNULL(service_vouchers.total_net_amount, 0) + IFNULL(service_vouchers.total_tax_amount, 0)) AS total_gross_amount'),
            DB::raw('(IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount) - IFNULL(receipt_vouchers.amount, 0) + IFNULL(refund_vouchers.amount, 0)) + (IFNULL(service_vouchers.total_net_amount, 0) + IFNULL(service_vouchers.total_tax_amount, 0)) AS current_invoice_amount'),

            DB::raw('IFNULL(receipt_vouchers.count, 0) AS count_receipt_vouchers'),
            DB::raw('IFNULL(refund_vouchers.count, 0) AS count_refund_vouchers'),
            DB::raw('IFNULL(service_vouchers.count, 0) AS count_service_vouchers')
        )
        ->leftJoinSub(
            Invoice::select('client_id', DB::raw('MAX(date) as date'))->groupBy('client_id'),
            'previous_invoice_ids', 'previous_invoice_ids.client_id', '=', 'clients.id'
        )
        ->leftJoinSub(
            Invoice::select('client_id', 'date', 'current_invoice_amount as amount'),
            'previous_invoices', 
            function (JoinClause $join) {
                $join->on('previous_invoices.client_id', '=', 'previous_invoice_ids.client_id')
                ->on('previous_invoices.date', '=', 'previous_invoice_ids.date');
            }
        )
        ->leftJoinSub(
            ReceiptVoucher::select(
                'receipt_vouchers.client_id', 
                DB::raw('SUM(receipt_voucher_bodies.amount) AS amount'), 
                DB::raw('COUNT(DISTINCT receipt_vouchers.id) AS count')
            )
            ->leftJoin('receipt_voucher_bodies', 'receipt_voucher_bodies.receipt_voucher_id', '=', 'receipt_vouchers.id')
            ->where('receipt_vouchers.date', '<=', $validated['date'])
            ->whereNull('receipt_vouchers.invoice_id')
            ->groupBy('receipt_vouchers.client_id'),
            'receipt_vouchers', 'receipt_vouchers.client_id', '=', 'clients.id'
        )
        ->leftJoinSub(
            RefundVoucher::select(
                'refund_vouchers.client_id', 
                DB::raw('SUM(refund_voucher_bodies.amount) AS amount'), 
                DB::raw('COUNT(DISTINCT refund_vouchers.id) AS count')
            )
            ->leftJoin('refund_voucher_bodies', 'refund_voucher_bodies.refund_voucher_id', '=', 'refund_vouchers.id')
            ->where('refund_vouchers.date', '<=', $validated['date'])
            ->whereNull('refund_vouchers.invoice_id')
            ->groupBy('refund_vouchers.client_id'),
            'refund_vouchers', 'refund_vouchers.client_id', '=', 'clients.id'
        )
        ->leftJoinSub(
            DB::table(
                Client::select(
                    'clients.id AS client_id',
                    DB::raw('SUM(service_voucher_footers.total_net_amount) AS total_net_amount'),
                    DB::raw('ROUND(SUM(service_voucher_footers.total_net_amount * service_voucher_footers.tax_rate_rate / 100)) AS total_tax_amount'),
                    DB::raw('COUNT(DISTINCT service_vouchers.id) AS count')
                )
                ->leftJoin('service_vouchers', 'service_vouchers.client_id', '=', 'clients.id')
                ->leftJoin('service_voucher_footers', 'service_voucher_footers.service_voucher_id', '=', 'service_vouchers.id')
                ->where('service_vouchers.date', '<=', $validated['date'])
                ->whereNull('service_vouchers.invoice_id')
                ->groupBy('clients.id', 'service_voucher_footers.tax_rate_id'),
                'table'
            )
            ->select(
                'table.client_id',
                DB::raw('SUM(table.total_net_amount) AS total_net_amount'),
                DB::raw('SUM(table.total_tax_amount) AS total_tax_amount'),
                DB::raw('ANY_VALUE(table.count) AS count')
            )
            ->groupBy('table.client_id'),
            'service_vouchers', 'service_vouchers.client_id', '=', 'clients.id'
        )
        ->where(function ($query) use ($validated) {
            $query->where('previous_invoices.date', '<', $validated['date']) // 今日よりも新しい日付の請求書がない
            ->orWhereNull('previous_invoices.date'); // 請求書を作ったことがない
        });

        // 支払方法
        if(isset($validated['billing_cycle_type_id']) and !empty($validated['billing_cycle_type_id'])){
            $query->where('clients.billing_cycle_type_id', $validated['billing_cycle_type_id']);
        }

        // 取引区分
        if(isset($validated['payment_method_id']) and !empty($validated['payment_method_id'])){
            $query->where('clients.payment_method_id', $validated['payment_method_id']);
        }

        // 締め区分・締め日
        if(isset($validated['transaction_type_id']) and !empty($validated['transaction_type_id'])){
            $query->where('clients.transaction_type_id', $validated['transaction_type_id']);
        }

        // 担当者
        if(isset($validated['user_id']) and !empty($validated['user_id'])){
            $query->where('clients.user_id', $validated['user_id']);
        }

        // 請求書の内容がない得意先は非表示
        if(isset($validated['ignore_if_empty']) and !empty($validated['ignore_if_empty'])){
            $query = $query->whereNot(function ($query) {
                $query->where(DB::raw('IFNULL(receipt_vouchers.count, 0)'), 0)
                ->where(DB::raw('IFNULL(refund_vouchers.count, 0)'), 0)
                ->where(DB::raw('IFNULL(service_vouchers.count, 0)'), 0)
                ->where(DB::raw('IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount)'), 0);
            });
        }

        // 得意先がログイン対応してるかどうか
        if(isset($validated['allow_login'])){
            if($validated['allow_login'] === false){
                $query->where('clients.allow_login', false);
            }else if($validated['allow_login'] === true){
                $query->where('clients.allow_login', true);
            }
        }

        return response()->json($query->orderBy('clients.code')->groupBy('clients.id')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function issue(PendingInvoiceRequest $request)
    {
        $validated = $request->validated();
        
        DB::transaction(function () use ($validated) {
            $clients = Client::select(
                'clients.id',
                'clients.code',
                'clients.name',
                'clients.postal',
                'clients.address',
                'clients.tel',
                'clients.fax',
                'name_suffixes.id as name_suffix_id',
                'name_suffixes.value as name_suffix_value',

                DB::raw('IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount) as previous_invoice_amount')                
            )
            ->whereIn('clients.id', $validated['client_ids'])
            ->leftJoin('name_suffixes', 'name_suffixes.id', '=', 'clients.name_suffix_id')
            ->leftJoinSub(
                Invoice::select('client_id', DB::raw('MAX(date) as date'))->groupBy('client_id'),
                'previous_invoice_ids', 'previous_invoice_ids.client_id', '=', 'clients.id'
            )
            ->leftJoinSub(
                Invoice::select('client_id', 'date', 'current_invoice_amount as amount'),
                'previous_invoices', 
                function (JoinClause $join) {
                    $join->on('previous_invoices.client_id', '=', 'previous_invoice_ids.client_id')
                    ->on('previous_invoices.date', '=', 'previous_invoice_ids.date');
                }
            )
            ->get()
            ->keyBy('id');
            
            $settings = Settings::firstOrFail();
            $invoice_ids_with_client_ids = [];

            foreach($validated['client_ids'] as $client_id){
                $client = $clients->get($client_id);

                // まず空のヘッダを作る
                $header = Invoice::create([
                    'date' => $validated['date'],

                    'client_id' => $client->id,
                    'client_code' => $client->code,
                    'client_name' => $client->name,
                    'client_postal' => $client->postal,
                    'client_address' => $client->address,

                    'name_suffix_id' => $client->name_suffix_id,
                    'name_suffix_value' => $client->name_suffix_value,

                    'previous_invoice_amount' => $client->previous_invoice_amount,
                    'total_receipt_amount' => 0,
                    'total_refund_amount' => 0,
                    'carried_forward_amount' => 0,
                    
                    'total_net_amount' => 0,
                    'total_tax_amount' => 0,
                    'total_gross_amount' => 0,
                    'current_invoice_amount' => 0,

                    'my_name' => $settings->name,
                    'my_postal' => $settings->postal,
                    'my_address' => $settings->address,
                    'my_tel' => $settings->tel,
                    'my_fax' => $settings->fax,
                ]);

                $update_invoice_id_column_scope = function ($query) use ($validated, $client_id) {
                    $query->where('client_id', $client_id)
                    ->where('date', '<=', $validated['date'])
                    ->whereNull('invoice_id');
                };

                ServiceVoucher::where($update_invoice_id_column_scope)->update(['invoice_id' => $header->id]);
                ReceiptVoucher::where($update_invoice_id_column_scope)->update(['invoice_id' => $header->id]);
                RefundVoucher::where($update_invoice_id_column_scope)->update(['invoice_id' => $header->id]);

                $invoice_ids_with_client_ids[] = [
                    'invoice_id' => $header->id,
                    'client_id' => $client_id,
                    'header' => $header
                ];
            }

            $invoice_ids = array_column($invoice_ids_with_client_ids, 'invoice_id');

            // 入出金
            $total_receipts = ReceiptVoucher::whereIn('invoice_id', $invoice_ids)
            ->select('invoice_id', DB::raw('SUM(total_amount) as amount'))
            ->groupBy('invoice_id')->get()->pluck('amount', 'invoice_id');

            $total_refunds = RefundVoucher::whereIn('invoice_id', $invoice_ids)
            ->select('invoice_id', DB::raw('SUM(total_amount) as amount'))
            ->groupBy('invoice_id')->get()->pluck('amount', 'invoice_id');

            // 今回分、消費税、合計
            $total_service_vouchers = ServiceVoucher::query()
            ->select(
                'service_vouchers.invoice_id',
                DB::raw('SUM(service_voucher_footers.total_net_amount) AS total_net_amount'),
                DB::raw('ROUND(SUM(service_voucher_footers.total_net_amount * service_voucher_footers.tax_rate_rate / 100)) AS total_tax_amount'),
                'service_voucher_footers.tax_rate_id as tax_rate_id',
                'service_voucher_footers.tax_rate_rate as tax_rate_rate',
                'service_voucher_footers.tax_rate_name as tax_rate_name',
            )
            ->join('service_voucher_footers', 'service_voucher_footers.service_voucher_id', '=', 'service_vouchers.id')
            ->whereIn('service_vouchers.invoice_id', $invoice_ids)
            ->groupBy(
                'service_vouchers.invoice_id',
                'service_voucher_footers.tax_rate_id', 
                'service_voucher_footers.tax_rate_rate',
                'service_voucher_footers.tax_rate_name',
            )
            ->get()
            ->groupBy('invoice_id');

            foreach($invoice_ids_with_client_ids as $invoice_id_with_client_id){
                $invoice_id = $invoice_id_with_client_id['invoice_id'];
                $client_id = $invoice_id_with_client_id['client_id'];
                $header = $invoice_id_with_client_id['header'];

                $total_service_voucher = $total_service_vouchers->get($invoice_id, collect());

                $previous_invoice_amount = $clients->get($client_id)->previous_invoice_amount;
                
                $total_receipt_amount = $total_receipts->get($invoice_id, 0);
                $total_refund_amount = $total_refunds->get($invoice_id, 0);
                $carried_forward_amount = $previous_invoice_amount - $total_receipt_amount + $total_refund_amount;

                $total_net_amount = $total_service_voucher->sum('total_net_amount');
                $total_tax_amount = $total_service_voucher->sum('total_tax_amount');
                $total_gross_amount = $total_net_amount + $total_tax_amount;
                $current_invoice_amount = $total_gross_amount + $carried_forward_amount;

                $header->update([
                    'total_receipt_amount' => $total_receipt_amount,
                    'total_refund_amount' => $total_refund_amount,
                    'carried_forward_amount' => $carried_forward_amount,
                    
                    'total_net_amount' => $total_net_amount,
                    'total_tax_amount' => $total_tax_amount,
                    'total_gross_amount' => $total_gross_amount,
                    'current_invoice_amount' => $current_invoice_amount,
                ]);

                $header->footers()->createMany($total_service_voucher->toArray());
            }
        });

        return response()->noContent(); // 204
    }

    public function request_pdf(PendingInvoiceRequest $request)
    {
        $validated = $request->validated();
        $uuid = (string) Str::uuid();

        Cache::put($uuid, $validated);

        return response()->json(['uuid' => $uuid]);
    }

    public function pdf(string $uuid)
    {
        $params = Cache::pull($uuid);

        if(!$params) abort(404);

        $result = Client::query()
        ->select(
            'clients.id AS client_id',
            'clients.name AS client_name',
            'clients.code AS client_code',
            DB::raw('IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount) AS previous_invoice_amount'),
            DB::raw('IFNULL(receipt_vouchers.amount, 0) AS total_receipt_amount'),
            DB::raw('IFNULL(refund_vouchers.amount, 0) AS total_refund_amount'),
            DB::raw('(IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount) - IFNULL(receipt_vouchers.amount, 0) + IFNULL(refund_vouchers.amount, 0)) AS carried_forward_amount'),
            DB::raw('IFNULL(service_vouchers.total_net_amount, 0) AS total_net_amount'),
            DB::raw('IFNULL(service_vouchers.total_tax_amount, 0) AS total_tax_amount'),
            DB::raw('(IFNULL(service_vouchers.total_net_amount, 0) + IFNULL(service_vouchers.total_tax_amount, 0)) AS total_gross_amount'),
            DB::raw('(IFNULL(previous_invoices.amount, clients.initial_previous_invoice_amount) - IFNULL(receipt_vouchers.amount, 0) + IFNULL(refund_vouchers.amount, 0)) + (IFNULL(service_vouchers.total_net_amount, 0) + IFNULL(service_vouchers.total_tax_amount, 0)) AS current_invoice_amount'),
        )
        ->leftJoinSub(
            Invoice::select('client_id', DB::raw('MAX(date) as date'))->groupBy('client_id'),
            'previous_invoice_ids', 'previous_invoice_ids.client_id', '=', 'clients.id'
        )
        ->leftJoinSub(
            Invoice::select('client_id', 'date', 'current_invoice_amount as amount'),
            'previous_invoices', 
            function (JoinClause $join) {
                $join->on('previous_invoices.client_id', '=', 'previous_invoice_ids.client_id')
                ->on('previous_invoices.date', '=', 'previous_invoice_ids.date');
            }
        )
        ->leftJoinSub(
            ReceiptVoucher::select(
                'receipt_vouchers.client_id', 
                DB::raw('SUM(receipt_voucher_bodies.amount) AS amount')
            )
            ->leftJoin('receipt_voucher_bodies', 'receipt_voucher_bodies.receipt_voucher_id', '=', 'receipt_vouchers.id')
            ->where('receipt_vouchers.date', '<=', $params['date'])
            ->whereIn('receipt_vouchers.client_id', $params['client_ids'])
            ->whereNull('receipt_vouchers.invoice_id')
            ->groupBy('receipt_vouchers.client_id'),
            'receipt_vouchers', 'receipt_vouchers.client_id', '=', 'clients.id'
        )
        ->leftJoinSub(
            RefundVoucher::select(
                'refund_vouchers.client_id', 
                DB::raw('SUM(refund_voucher_bodies.amount) AS amount')
            )
            ->leftJoin('refund_voucher_bodies', 'refund_voucher_bodies.refund_voucher_id', '=', 'refund_vouchers.id')
            ->where('refund_vouchers.date', '<=', $params['date'])
            ->whereIn('refund_vouchers.client_id', $params['client_ids'])
            ->whereNull('refund_vouchers.invoice_id')
            ->groupBy('refund_vouchers.client_id'),
            'refund_vouchers', 'refund_vouchers.client_id', '=', 'clients.id'
        )
        ->leftJoinSub(
            DB::table(
                Client::select(
                    'clients.id AS client_id',
                    DB::raw('SUM(service_voucher_footers.total_net_amount) AS total_net_amount'),
                    DB::raw('ROUND(SUM(service_voucher_footers.total_net_amount * service_voucher_footers.tax_rate_rate / 100)) AS total_tax_amount')
                )
                ->leftJoin('service_vouchers', 'service_vouchers.client_id', '=', 'clients.id')
                ->leftJoin('service_voucher_footers', 'service_voucher_footers.service_voucher_id', '=', 'service_vouchers.id')
                ->where('service_vouchers.date', '<=', $params['date'])
                ->whereIn('service_vouchers.client_id', $params['client_ids'])
                ->whereNull('service_vouchers.invoice_id')
                ->groupBy('clients.id', 'service_voucher_footers.tax_rate_id'),
                'table'
            )
            ->select(
                'table.client_id',
                DB::raw('SUM(table.total_net_amount) AS total_net_amount'),
                DB::raw('SUM(table.total_tax_amount) AS total_tax_amount')
            )
            ->groupBy('table.client_id'),
            'service_vouchers', 'service_vouchers.client_id', '=', 'clients.id'
        )
        ->whereIn('clients.id', $params['client_ids'])
        ->where(function ($query) use ($params) {
            $query->where('previous_invoices.date', '<', $params['date']) // 今日よりも新しい日付の請求書がない
            ->orWhereNull('previous_invoices.date'); // 請求書を作ったことがない
        })
        ->orderBy('clients.code')
        ->groupBy('clients.id')
        ->get();

        // PDF処理ここから

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false, false);
        $font = new TCPDF_FONTS();

        $NotoSansJP = $font->addTTFfont(Storage::disk('local')->path('fonts/NotoSansJP-Regular.ttf'));

        $pdf->SetFont($NotoSansJP , '', 10);

        $w = $pdf->GetPageWidth() - 20; // マージン抜き
        $h = $pdf->GetPageHeight() - 30; // マージン抜き
    
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setMargins(10, 20, 10);

        $perPage = 22;
        $allPages = ceil(count($result)/$perPage); // 一ページに何行表示するか
        $now = Carbon::now()->format('Y年m月d日 H時i分s秒');
        $date = Carbon::parse($params['date'])->format('Y年m月d日');

        for($p=0; $p<$allPages; $p++){
            $pdf->addPage();

            // タイトルとヘッダここから

            $pdf->setFontSize(16);
            $pdf->Cell($w, 0, '請求予定額一覧', 0, 0, 'L');

            $pdf->setXY(10, 20);

            $currentPage = $p + 1;
            $pdf->setFontSize(8);
            $pdf->Cell($w, 0, "全{$allPages}ページ中{$currentPage}ページ目", 0, 1, 'R');

            $pdf->setFontSize(8);
            $pdf->Cell($w, 0, $date . ' 請求予定分 ' . $now . ' 発行', 0, 1, 'R');

            // タイトルとヘッダここまで

            $pdf->setXY(10, $pdf->getY() + 4);
            $pdf->setFontSize(10);

            // ここからテーブル

            $tableCellHeight = 7;
            $cw = $w/100;

            $pdf->Cell($cw*28, $tableCellHeight, '顧客', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '前回請求額', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '御入金額', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '返金額', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '繰越額', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '今回分', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '消費税', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '今回合計', 1, 0, 'C');
            $pdf->Cell($cw*9, $tableCellHeight, '今回請求額', 1, 1, 'C');

            for($i=0; $i<$perPage; $i++){
                if(isset($result[$i + ($p * $perPage)])){
                    $line = $result[$i + ($p * $perPage)];

                    $pdf->Cell($cw*28, $tableCellHeight, '[' . $line->client_code . '] ' . $line->client_name, 1, 0, 'L');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->previous_invoice_amount), 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->total_receipt_amount), 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->total_refund_amount), 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->carried_forward_amount), 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->total_net_amount), 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->total_tax_amount), 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->total_gross_amount), 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, number_format($line->current_invoice_amount), 1, 1, 'R');
                }else{
                    $pdf->Cell($cw*28, $tableCellHeight, null, 1, 0, 'L');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 0, 'R');
                    $pdf->Cell($cw*9, $tableCellHeight, null, 1, 1, 'R');
                }
            }

            $pdf->Cell($cw*28, $tableCellHeight, '総合計', 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('previous_invoice_amount')), 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('total_receipt_amount')), 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('total_refund_amount')), 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('carried_forward_amount')), 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('total_net_amount')), 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('total_tax_amount')), 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('total_gross_amount')), 1, 0, 'R');
            $pdf->Cell($cw*9, $tableCellHeight, number_format($result->sum('current_invoice_amount')), 1, 1, 'R');
        }

        $pdf->Output("{$uuid}.pdf", 'I');
    }
}
