<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ EstimateVoucher, TaxRate };
use App\Http\Requests\EstimateVoucherRequest;
use Spatie\QueryBuilder\{ QueryBuilder, AllowedFilter };
use Spatie\QueryBuilder\Enums\FilterOperator;
use App\Filters\EstimateVoucherKeywordSearch;
use TCPDF;
use TCPDF_FONTS;
use Illuminate\Support\Facades\{ DB, Storage };
use Carbon\Carbon;
use Illuminate\Support\Arr;

class EstimateVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = QueryBuilder::for(EstimateVoucher::class)
        ->allowedFilters([
            AllowedFilter::custom('keywords', new EstimateVoucherKeywordSearch()), // キーワード検索

            AllowedFilter::operator('date_from', FilterOperator::GREATER_THAN_OR_EQUAL, 'date'), // date 範囲
            AllowedFilter::operator('date_to', FilterOperator::LESS_THAN_OR_EQUAL, 'date'), // date 範囲

            AllowedFilter::operator('expiry_date_from', FilterOperator::GREATER_THAN_OR_EQUAL, 'expiry_date'), // date 範囲
            AllowedFilter::operator('expiry_date_to', FilterOperator::LESS_THAN_OR_EQUAL, 'expiry_date'), // date 範囲

            AllowedFilter::exact('user_id'), // id 完全一致
            AllowedFilter::exact('client_id'), // id 完全一致
            AllowedFilter::exact('name_suffix_id'), // id 完全一致
        ])
        ->defaultSort('id')
        ->with('client', 'name_suffix', 'user', 'client.name_suffix')
        ->allowedSorts(['id', 'date', 'expiry_date', 'created_at', 'updated_at'])
        ->paginate(100); // 一ページ当たり100件

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EstimateVoucherRequest $request)
    {
        $estimate_voucher = DB::transaction(function () use ($request) {
            $bodies_and_footers = $this->build_bodies_and_footers($request->validated());

            $voucher = EstimateVoucher::create([
                ...$request->validated(), 
                'total_net_amount' => $bodies_and_footers['total_net_amount'],
            ]);

            $voucher->bodies()->createMany($bodies_and_footers['bodies']);
            $voucher->footers()->createMany($bodies_and_footers['footers']);

            return $voucher;
        });

        return response()->json($estimate_voucher, 201); // 201 作成したデータを返す
    }

    /**
     * Display the specified resource.
     */
    public function show(EstimateVoucher $estimate_voucher)
    {
        return response()->json($estimate_voucher->fresh([
            'bodies', 
            'footers', 
            'user', 
            'name_suffix', 
            
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
    public function update(EstimateVoucherRequest $request, EstimateVoucher $estimate_voucher)
    {
        DB::transaction(function () use ($request, $estimate_voucher) {
            // これでいいの...?
            $estimate_voucher = EstimateVoucher::lockForUpdate()->findOrFail($estimate_voucher->id);

            $bodies_and_footers = $this->build_bodies_and_footers($request->validated());
            $bodies = $bodies_and_footers['bodies'];
            $footers = $bodies_and_footers['footers'];
            
            $estimate_voucher->update([
                ...$request->validated(), 
                'total_net_amount' => $bodies_and_footers['total_net_amount'],
            ]); // ヘッダをアップデート

            // ない行を削除
            $estimate_voucher->bodies()->whereNotIn('id', $bodies->pluck('id')->filter())->delete();
            $estimate_voucher->footers()->whereNotIn('tax_rate_id', $footers->pluck('tax_rate_id')->all())->delete();

            // 更新 or 登録
            $bodies->each(fn ($body) => $estimate_voucher->bodies()->updateOrCreate(['id' => $body['id'] ?? null], $body));
            $footers->each(fn ($footer) => $estimate_voucher->footers()->updateOrCreate(['tax_rate_id' => $footer['tax_rate_id']], $footer));
        });

        return response()->json($estimate_voucher->fresh()); // 200 編集した新しいデータを返す（データベースの今の状態を返す
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EstimateVoucher $estimate_voucher)
    {
        $estimate_voucher->delete();

        return response()->noContent(); // 204
        // もしかしたら削除したデータに関して何か返したほうが、フロントが楽になるかもしれない
    }

    public function pdf(EstimateVoucher $estimate_voucher)
    {
        // データ加工
        $voucher_bodies = $estimate_voucher->bodies->map(function ($body) {
            $content = null;
            $tax_rate_name = null;
            $quantity = null;
            $unit_price = null;
            $total = null;

            if(!empty($body)){
                $content = $body->content;

                if($body->tax_rate_mark) $content = $body->content . ' ' . $body->tax_rate_mark;

                $tax_rate_name = $body->tax_rate_name;

                if($body->quantity) $quantity = number_format((int) $body->quantity);
                if($body->unit_price) $unit_price = number_format((int) $body->unit_price);

                if(in_array($body->estimate_voucher_row_type_id, [1,2,3], true)){
                    $total = number_format((int) $body->quantity * (int) $body->unit_price);
                }
            }

            return [
                'type' => 'body',
                'estimate_voucher_row_type_id' => $body->estimate_voucher_row_type_id,
                'content' => $content,
                'tax_rate_name' => $tax_rate_name,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'total' => $total
            ];
        });

        $voucher_footers = $estimate_voucher->footers->map(function ($footer) {
            $tax_rate_name = null;
            $total_net_amount = null;

            if($footer->tax_rate_name) $tax_rate_name = $footer->tax_rate_name;
            if($footer->total_net_amount) $total_net_amount = number_format((int) $footer->total_net_amount);

            return [
                'type' => 'footer',
                'title' => $tax_rate_name,
                'total_net_amount' => $total_net_amount
            ];
        });

        $voucher_contents = [
            ...$voucher_bodies,
            ...$voucher_footers,
            [
                'type' => 'footer',
                'title' => '合計',
                'total_net_amount' => number_format((int) $estimate_voucher->total_net_amount)
            ]
        ];

        $client_name = [null, null];

        if(!$estimate_voucher->extension_client_name){
            $client_name[0] = "{$estimate_voucher->client_name} {$estimate_voucher->client->name_suffix->value}";
        }else{
            $client_name[0] = "{$estimate_voucher->client_name}";
            $client_name[1] = "{$estimate_voucher->extension_client_name} {$estimate_voucher->name_suffix->value}";
        }

        $client_postal = null;

        if($estimate_voucher->client_postal){
            $client_postal = '〒' . $estimate_voucher->client_postal;
        }

        $my_postal = null;

        if($estimate_voucher->my_postal){
            $my_postal = '〒' . $estimate_voucher->my_postal;
        }

        // データ加工ここまで

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false, false);
        $font = new TCPDF_FONTS();

        $NotoSansJP = $font->addTTFfont(Storage::disk('local')->path('fonts/NotoSansJP-Regular.ttf'));

        $pdf->SetFont($NotoSansJP , '', 10);

        // マージン抜きの幅
        $w = ($pdf->GetPageWidth() - 30) / 100;
        $h = ($pdf->GetPageHeight() - 20) / 100;
    
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setMargins(20, 10, 10);
        $pdf->setFillColor(240);

        $perPage = 20;
        $allPages = ceil(count($voucher_contents)/$perPage);

        for($p=0; $p<$allPages; $p++){
            // ループここから
            $pdf->addPage();

            // タイトル
            $pdf->setFontSize(20);
            $pdf->Cell($w*60, 9, '御見積書', 0, 0, 'L');

            $pdf->setLeftMargin($w*60 + 20);

            $currentPage = $p + 1;

            $pdf->setFontSize(8);
            $pdf->Cell($w*40, 0, "全{$allPages}ページ中{$currentPage}ページ目", 0, 1, 'R');

            $pdf->setFontSize(10);
            $pdf->Cell($w*20, 0, '日付', 0, 0, 'R');
            $pdf->Cell($w*20, 0, Carbon::parse($estimate_voucher->date)->format('Y年m月d日'), 0, 1, 'R');

            $pdf->Cell($w*20, 0, '有効期限', 0, 0, 'R');
            $pdf->Cell($w*20, 0, Carbon::parse($estimate_voucher->expiry_date)->format('Y年m月d日'), 0, 1, 'R');

            $pdf->setLeftMargin(20);

            // タイトルの下の線
            $pdf->SetLineStyle(['dash' => '7', 'color' => [0, 104, 183], 'width' => 0.4]);
            $pdf->Line(20, 20, $w*60 + 20, 20);
            $pdf->Line(20, 21, $w*60 + 20, 21);
            $pdf->SetLineStyle(['dash' => '0', 'color' => [0], 'width' => 0.2]);

            // 得意先の表示ここから

            // 得意先名称
            $pdf->setFontSize(12);
            $pdf->setXY(20, 30); // キリがいいので30からスタート
            $pdf->Cell($w*50, 6, $client_name[0], 0, 1, 'L', false, '', 1);
            $pdf->Line(20, $pdf->getY(), ($w * 50) + 20, $pdf->getY());
            
            $pdf->setFontSize(11);
            $pdf->setY(38);
            $pdf->Cell($w*40, 5, $client_name[1], 0, 1, 'L', false, '', 1);
            $pdf->Line(20, $pdf->getY(), ($w * 40) + 20, $pdf->getY());

            // 得意先住所
            $pdf->setFontSize(10);
            $pdf->setY(45);
            $pdf->Cell($w*50, 5, $client_postal, 0, 1, '', false, '', 1);
            $pdf->Cell($w*50, 5, $estimate_voucher->client_address, 0, 1, '', false, '', 1);

            // 得意先電話番号
            $pdf->setY(57);

            if($estimate_voucher->client_tel){
                $pdf->Cell($w*10, 5, 'TEL', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $estimate_voucher->client_tel, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            if($estimate_voucher->client_fax){
                $pdf->Cell($w*10, 5, 'FAX', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $estimate_voucher->client_fax, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            // 得意先の表示ここまで

            // 総額表示
            $pdf->setFontSize(12);
            $pdf->setY(71);
            $pdf->Cell($w*15, 13, '合計', 1, 0, 'C');
            $pdf->Cell($w*35, 13, '¥ ' . number_format((int) $estimate_voucher->total_net_amount) . ' -', 1, 1, 'C', false, '', 1);
            

            // 自社情報の表示ここから
            $pdf->setFontSize(11);
            $pdf->setLeftMargin(($w * 60) + 20);
            $pdf->setY(38);

            $pdf->Cell($w*40, 5, $estimate_voucher->my_name, 0, 1, 'L', false, '', 1);
            $pdf->Line(($w * 60) + 20, $pdf->getY(), ($w * 100) + 20, $pdf->getY());

            $pdf->setFontSize(10);

            $pdf->setY(45);
            $pdf->Cell($w*40, 5, $my_postal, 0, 2, '', false, '', 1);
            $pdf->Cell($w*40, 5, $estimate_voucher->my_address, 0, 2, '', false, '', 1);

            $pdf->setY(57);

            if($estimate_voucher->my_tel){
                $pdf->Cell($w*10, 5, 'TEL', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $estimate_voucher->my_tel, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            if($estimate_voucher->my_fax){
                $pdf->Cell($w*10, 5, 'FAX', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $estimate_voucher->my_fax, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            $pdf->setLeftMargin(20);

            // 伝票の内容ここから

            $pdf->setY(88);
            $pdf->setFontSize(10);

            $tableCellHeight = 7;

            $pdf->Cell($w*50, $tableCellHeight, '内容', 0, 0, 'C');
            $pdf->Cell($w*12, $tableCellHeight, '税率区分', 0, 0, 'C');
            $pdf->Cell($w*12, $tableCellHeight, '数量', 0, 0, 'C');
            $pdf->Cell($w*12, $tableCellHeight, '単価', 0, 0, 'C');
            $pdf->Cell($w*14, $tableCellHeight, '合計', 0, 1, 'C');

            $pdf->Line(20, 95, ($w * 100) + 20, 95);

            for($i=0; $i<$perPage; $i++){
                $line = Arr::get($voucher_contents, $i + ($p * $perPage), null);

                if(!empty($line)){
                    switch(Arr::get($line, 'type', null)){
                        case 'body':
                            if(Arr::get($line, 'estimate_voucher_row_type_id', null) === 4){
                                $pdf->setCellPaddings(4, 0, 0, 0);

                                $pdf->Cell($w*100, $tableCellHeight, $content, 0, 1, 'L', !($i%2), '', 1);

                                $pdf->setCellPaddings(1, 0, 1, 0);
                            }else{
                                $pdf->Cell($w*50, $tableCellHeight, Arr::get($line, 'content', null), 0, 0, 'L', !($i%2), '', 1);
                                $pdf->Cell($w*12, $tableCellHeight, Arr::get($line, 'tax_rate_name', null), 0, 0, 'C', !($i%2), '', 1);
                                $pdf->Cell($w*12, $tableCellHeight, Arr::get($line, 'quantity', null), 0, 0, 'R', !($i%2), '', 1);
                                $pdf->Cell($w*12, $tableCellHeight, Arr::get($line, 'unit_price', null), 0, 0, 'R', !($i%2), '', 1);
                                $pdf->Cell($w*14, $tableCellHeight, Arr::get($line, 'total', null), 0, 1, 'R', !($i%2), '', 1);
                            }

                            break;
                        case 'footer':
                            $pdf->Cell($w*86, $tableCellHeight, Arr::get($line, 'title', null), 0, 0, 'R', !($i%2), '', 1);
                            $pdf->Cell($w*14, $tableCellHeight, Arr::get($line, 'total_net_amount', null), 0, 1, 'R', !($i%2), '', 1);
                            break;
                    }
                }else{
                    // 空白 スペース埋め
                    $pdf->Cell($w*100, $tableCellHeight, null, 0, 1, 'L', !($i%2), '', 1);
                }
            }
            // 伝票の内容ここまで

            $pdf->setFontSize(8);
            $pdf->Cell(0, 0, '※消費税は請求時に別途申し受けます', 0, 1, 'L', false, '', 0, false, 'T', 'T');

            // 備考欄などここから
            $pdf->setFontSize(10);
            $pdf->Line(20, 240, ($w * 100) + 20, 240); // 上
            $pdf->Line(20, ($h * 100) + 10, ($w * 100) + 20, ($h * 100) + 10); // 下
            $pdf->Line(20, 240, 20, ($h * 100) + 10); // 左
            $pdf->Line(($w * 100) + 20, 240, ($w * 100) + 20, ($h * 100) + 10); // 右

            $pdf->setY(240);
            $pdf->setFontSize(10);
            $pdf->Cell($w*100, 6, '備考欄', 0, 1, 'L', false, '', 1, false, 'T', 'M');

            $pdf->setFontSize(8);
            $pdf->setCellPaddings(1, 0, 1, 1);
            $pdf->multiCell($w*100, (($h*100) - $pdf->getY() + 10), $estimate_voucher->customer_note, 0, 'L', false, 1, '', '', true, 0, false, true, $h - ($pdf->getY() + 10), 'T', true);
            $pdf->setCellPaddings(1, 0, 1, 0);
            // 備考欄などここまで
        }

        $pdf->Output("{$estimate_voucher->id}.pdf", 'I');
    }

    private function build_bodies_and_footers(array $validated): array
    {
        $bodies = collect($validated['bodies']);
        $tax_rates = TaxRate::whereIn('id', $bodies->pluck('tax_rate_id')->filter())->get()->keyBy('id'); // 伝票に登録されている税率だけ引っこ抜いて取得

        $footers = $bodies->whereNotNull('tax_rate_id') // tax_rate_idがない行を消して(これでエラー避けができる)
        ->groupBy('tax_rate_id') // 税率でグルーピング
        ->map(function ($body, $tax_rate_id) use ($tax_rates) {
            $tax_rate = $tax_rates->get($tax_rate_id); // バリデーションで存在しない値は省いているはず
            $total_net_amount = $body->sum(fn($body) => $body['unit_price'] * $body['quantity']); // 行の合計額を計算してsumで集計

            return [
                'tax_rate_id' => $tax_rate_id,
                'tax_rate_rate' => $tax_rate->rate,
                'tax_rate_name' => $tax_rate->name,

                'total_net_amount' => $total_net_amount,
            ];
        })
        ->values();

        $bodies = $bodies->map(function ($body) use ($tax_rates) {
            if(empty($body['tax_rate_id'])){
                $body['tax_rate_rate'] = null;
                $body['tax_rate_name'] = null;
                $body['tax_rate_mark'] = null;
            }else{
                $tax_rate = $tax_rates->get($body['tax_rate_id']);

                $body['tax_rate_rate'] = $tax_rate->rate;
                $body['tax_rate_name'] = $tax_rate->name;
                $body['tax_rate_mark'] = $tax_rate->mark;
            }

            return $body;
        });

        return [
            'bodies' => collect($bodies),
            'footers' => collect($footers),
            'total_net_amount' => $footers->sum('total_net_amount'),
        ];
    }
}
