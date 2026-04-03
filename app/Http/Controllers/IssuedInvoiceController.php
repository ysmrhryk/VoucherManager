<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ Invoice, ServiceVoucher, ReceiptVoucher, RefundVoucher };
use TCPDF;
use TCPDF_FONTS;
use Illuminate\Support\Facades\{ DB, Log, Storage };
use Carbon\Carbon;
use Illuminate\Support\Arr;

class IssuedInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Invoice::all();

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->noContent(); // 204
    }

    public function pdf(Invoice $invoice)
    {
        $invoice = $invoice->fresh(['footers']);

        $service_vouchers = ServiceVoucher::select('id', 'date', 'total_net_amount', DB::raw('1 as vid'))->where('invoice_id', $invoice->id)->with(['bodies', 'footers'])->get();
        $receipt_vouchers = ReceiptVoucher::select('id', 'date', 'total_amount', DB::raw('2 as vid'))->where('invoice_id', $invoice->id)->with('bodies')->get();
        $refund_vouchers = RefundVoucher::select('id', 'date', 'total_amount', DB::raw('3 as vid'))->where('invoice_id', $invoice->id)->with('bodies')->get();

        $vouchers = $service_vouchers
        ->concat($receipt_vouchers)
        ->concat($refund_vouchers)
        ->sortBy([['date', 'asc'],['vid', 'asc']])
        ->values();

        $invoice_contents = [];

        // 内容用にデータ加工
        $vouchers->each(function ($voucher) use (&$invoice_contents) {
            switch($voucher->vid){
                case 1:
                    $voucher->bodies->each(function ($line, $index) use ($voucher, &$invoice_contents) {
                        $invoice_body_line = ['type' => 'body', 'vid' => $voucher->vid];

                        // 一行目に日付を持たせたい
                        if($index === 0) $invoice_body_line = [
                            ...$invoice_body_line,
                            'date' => $voucher->date
                        ];

                        // 変数初期化
                        $content = null;
                        $tax_rate_name = null;
                        $quantity = null;
                        $unit_price = null;
                        $total = null;

                        // 加工
                        $content = $line->content;

                        if($line->tax_rate_mark) $content = $line->content . ' ' . $line->tax_rate_mark;

                        $tax_rate_name = $line->tax_rate_name;

                        if($line->quantity) $quantity = number_format((int) $line->quantity);
                        if($line->unit_price) $unit_price = number_format((int) $line->unit_price);

                        if(in_array($line->service_voucher_row_type_id, [1,2], true)){
                            $total = number_format((int) $line->quantity * (int) $line->unit_price);
                        }

                        // 保存
                        $invoice_body_line = [
                            ...$invoice_body_line,
                            'service_voucher_row_type_id' => $line->service_voucher_row_type_id,
                            'content' => $content
                        ];

                        if(!(empty($line) and in_array($line->service_voucher_row_type_id, [3], true))){
                            $invoice_body_line = [
                                ...$invoice_body_line,
                                'tax_rate_name' => $tax_rate_name,
                                'quantity' => $quantity,
                                'unit_price' => $unit_price,
                                'total' => $total
                            ];
                        }

                        $invoice_contents[] = $invoice_body_line;
                    });

                    $voucher->footers->each(function ($footer) use (&$invoice_contents) {
                        $invoice_contents[] = [
                            'type' => 'footer',
                            'title' => $footer->tax_rate_name . ' 対象',
                            'total' => number_format($footer->total_net_amount)
                        ];
                    });

                    $invoice_contents[] = [
                        'type' => 'footer',
                        'title' => '【合計額】',
                        'total' => number_format($voucher->total_net_amount)
                    ];

                    // 余白
                    $invoice_contents[] = ['type' => 'space'];

                    break;
                case 2:
                    $voucher->bodies->each(function ($line, $index) use ($voucher, &$invoice_contents) {
                        $invoice_body_line = ['type' => 'body', 'vid' => $voucher->vid];

                        // 一行目に日付を持たせたい
                        if($index === 0) $invoice_body_line = [
                            ...$invoice_body_line,
                            'date' => $voucher->date
                        ];

                        // 変数初期化
                        $content = null;
                        $amount = null;

                        // 変数にデータを入れる
                        $content = $line->payment_method_value;

                        if($line->content) $content = $line->payment_method_value . '／' . $line->content;

                        // 保存
                        $invoice_contents[] = [
                            ...$invoice_body_line,
                            'content' => $content,
                            'amount' => $line->amount
                        ];
                    });

                    $invoice_contents[] = [
                        'type' => 'footer',
                        'title' => '【合計入金額】',
                        'total' => number_format($voucher->total_amount)
                    ];

                    // 余白
                    $invoice_contents[] = ['type' => 'space'];
                    
                    break;
                case 3:
                    $voucher->bodies->each(function ($line, $index) use ($voucher, &$invoice_contents) {
                        $invoice_body_line = ['type' => 'body', 'vid' => $voucher->vid];

                        // 一行目に日付を持たせたい
                        if($index === 0) $invoice_body_line = [
                            ...$invoice_body_line,
                            'date' => $voucher->date
                        ];

                        // 変数初期化
                        $content = null;
                        $amount = null;

                        // 変数にデータを入れる
                        $content = $line->payment_method_value;

                        if($line->content) $content = $line->payment_method_value . '／' . $line->content;

                        // 保存
                        $invoice_contents[] = [
                            ...$invoice_body_line,
                            'content' => $content,
                            'amount' => $line->amount
                        ];
                    });

                    $invoice_contents[] = [
                        'type' => 'footer',
                        'title' => '【合計返金額】',
                        'total' => number_format($voucher->total_amount)
                    ];

                    // 余白
                    $invoice_contents[] = ['type' => 'space'];

                    break;
            }
        });

        $invoice->footers->each(function ($footer) use (&$invoice_contents) {
            $invoice_contents[] = [
                'type' => 'invoice.footer',
                'total_net_amount' => number_format($footer->total_net_amount),
                'total_tax_amount' => number_format($footer->total_tax_amount),
                'tax_rate_name' => $footer->tax_rate_name,
            ];
        });

        $invoice_contents = collect($invoice_contents);

        $lineCount = $vouchers->map(function ($voucher) {
            // 2 は余白と合計額表示分
            return ($voucher->bodies->count() + 2) + ($voucher?->footers?->count());
        })->sum();

        $client_name = "{$invoice->client_name} {$invoice->name_suffix_value}";

        $client_postal = null;

        if($invoice->client_postal){
            $client_postal = '〒' . $invoice->client_postal;
        }

        $my_postal = null;

        if($invoice->my_postal){
            $my_postal = '〒' . $invoice->my_postal;
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false, false);
        $font = new TCPDF_FONTS();

        $NotoSansJP = $font->addTTFfont(Storage::disk('local')->path('fonts/NotoSansJP-Regular.ttf'));

        $pdf->SetFont($NotoSansJP , '', 10);

        $w = ($pdf->GetPageWidth() - 30) / 100; // マージン抜き
        $h = ($pdf->GetPageHeight() - 20) / 100; // マージン抜き
    
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setMargins(20, 10, 10);
        $pdf->setFillColor(240);

        $linePerPage = 31; // 一ページに何行表示するか
        $lineInPage = 0;
        $allPages = ceil($lineCount/$linePerPage);
        $date = Carbon::parse($invoice->date)->format('Y年m月d日');

        for($p=0; $p<$allPages; $p++){
            // ループここから
            $pdf->addPage();

            // タイトル
            $pdf->setFontSize(20);
            $pdf->Cell($w*60, 9, '御請求書', 0, 0, 'L');

            $pdf->setLeftMargin($w*60 + 20);

            $currentPage = $p + 1;

            $pdf->setFontSize(8);
            $pdf->Cell($w*40, 0, "全{$allPages}ページ中{$currentPage}ページ目", 0, 1, 'R');

            $pdf->setFontSize(10);
            $pdf->Cell($w*20, 0, '日付', 0, 0, 'R');
            $pdf->Cell($w*20, 0, Carbon::parse($invoice->date)->format('Y年m月d日'), 0, 1, 'R');

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
            $pdf->Cell($w*50, 6, $client_name, 0, 1, 'L', false, '', 1);
            $pdf->Line(20, $pdf->getY(), ($w * 50) + 20, $pdf->getY());

            // 得意先住所
            $pdf->setFontSize(10);
            $pdf->setY(38);
            $pdf->Cell($w*50, 5, $client_postal, 0, 1, '', false, '', 1);
            $pdf->Cell($w*50, 5, $invoice->client_address, 0, 1, '', false, '', 1);

            // 得意先電話番号
            $pdf->setY(50);

            if($invoice->client_tel){
                $pdf->Cell($w*10, 5, 'TEL', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $invoice->client_tel, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            if($invoice->client_fax){
                $pdf->Cell($w*10, 5, 'FAX', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $invoice->client_fax, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            // 得意先の表示ここまで

            // 自社情報の表示ここから
            $pdf->setFontSize(11);
            $pdf->setLeftMargin(($w * 60) + 20);
            $pdf->setY(38);

            $pdf->Cell($w*40, 5, $invoice->my_name, 0, 1, 'L', false, '', 1);
            $pdf->Line(($w * 60) + 20, $pdf->getY(), ($w * 100) + 20, $pdf->getY());

            $pdf->setFontSize(10);
            $pdf->setY(45);

            $pdf->setY(45);
            $pdf->Cell($w*40, 5, $my_postal, 0, 2, '', false, '', 1);
            $pdf->Cell($w*40, 5, $invoice->my_address, 0, 2, '', false, '', 1);

            $pdf->setY(57);

            if($invoice->my_tel){
                $pdf->Cell($w*10, 5, 'TEL', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $invoice->my_tel, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            if($invoice->my_fax){
                $pdf->Cell($w*10, 5, 'FAX', 1, 0, 'C');
                $pdf->Cell($w*20, 5, $invoice->my_fax, 0, 0, 'C', false, '', 1);
                $pdf->Cell($w*20, 5, null, 0, 1, 'C', false, '', 1); // 余白分
            }

            $pdf->setLeftMargin(20);

            // ヘッダここから
            $pdf->setY(71); // 57 + 5 + 5 + 4

            if($invoice->total_refund_amount){
                $pdf->Cell($w*12.5, 6, '前回請求額', 1, 0, 'C');
                $pdf->Cell($w*12.5, 6, '御入金額', 1, 0, 'C');
                $pdf->Cell($w*12.5, 6, '返金額', 1, 0, 'C');
                $pdf->Cell($w*12.5, 6, '繰越額', 1, 0, 'C');
                $pdf->Cell($w*12.5, 6, '今回分', 1, 0, 'C');
                $pdf->Cell($w*12.5, 6, '消費税', 1, 0, 'C');
                $pdf->Cell($w*12.5, 6, '今回合計', 1, 0, 'C');
                $pdf->Cell($w*12.5, 6, '今回請求額', 1, 1, 'C');

                $pdf->setFontSize(12);

                $pdf->Cell($w*12.5, 12, number_format($invoice->previous_invoice_amount), 1, 0, 'R');
                $pdf->Cell($w*12.5, 12, number_format($invoice->total_receipt_amount), 1, 0, 'R');
                $pdf->Cell($w*12.5, 12, number_format($invoice->total_refund_amount), 1, 0, 'R');
                $pdf->Cell($w*12.5, 12, number_format($invoice->carried_forward_amount), 1, 0, 'R');
                $pdf->Cell($w*12.5, 12, number_format($invoice->total_net_amount), 1, 0, 'R');
                $pdf->Cell($w*12.5, 12, number_format($invoice->total_tax_amount), 1, 0, 'R');
                $pdf->Cell($w*12.5, 12, number_format($invoice->total_gross_amount), 1, 0, 'R');
                $pdf->Cell($w*12.5, 12, number_format($invoice->current_invoice_amount), 1, 1, 'R');

                $pdf->setY(93);
            }else{
                $pdf->Cell($w*14, 6, '前回請求額', 1, 0, 'C');
                $pdf->Cell($w*14, 6, '御入金額', 1, 0, 'C');
                $pdf->Cell($w*14, 6, '繰越額', 1, 0, 'C');
                $pdf->Cell($w*14, 6, '今回分', 1, 0, 'C');
                $pdf->Cell($w*14, 6, '消費税', 1, 0, 'C');
                $pdf->Cell($w*14, 6, '今回合計', 1, 0, 'C');
                $pdf->Cell($w*16, 6, '今回請求額', 1, 1, 'C');

                $pdf->setFontSize(12);

                $pdf->Cell($w*14, 8, number_format($invoice->previous_invoice_amount), 1, 0, 'R');
                $pdf->Cell($w*14, 8, number_format($invoice->total_receipt_amount), 1, 0, 'R');
                $pdf->Cell($w*14, 8, number_format($invoice->carried_forward_amount), 1, 0, 'R');
                $pdf->Cell($w*14, 8, number_format($invoice->total_net_amount), 1, 0, 'R');
                $pdf->Cell($w*14, 8, number_format($invoice->total_tax_amount), 1, 0, 'R');
                $pdf->Cell($w*14, 8, number_format($invoice->total_gross_amount), 1, 0, 'R');
                $pdf->Cell($w*16, 8, number_format($invoice->current_invoice_amount), 1, 1, 'R');

                $pdf->setY(89);
            }

            // ヘッダここまで

            // 内容ここから
            $pdf->setFontSize(10);

            $pdf->Cell($w*12, 6, '日付', 0, 0, 'C');
            $pdf->Cell($w*40, 6, '内容', 0, 0, 'C');
            $pdf->Cell($w*12, 6, '税率区分', 0, 0, 'C');
            $pdf->Cell($w*12, 6, '数量', 0, 0, 'C');
            $pdf->Cell($w*12, 6, '単価', 0, 0, 'C');
            $pdf->Cell($w*12, 6, '合計', 0, 1, 'C');

            $pdf->Line(20, $pdf->getY(), $w * 100 + 20, $pdf->getY());

            for($i=0; $i<$linePerPage; $i++){
                $line = $invoice_contents->get($i + ($linePerPage * $p), []);

                switch(Arr::get($line, 'type', null)){
                    case 'body':
                        if(Arr::get($line, 'vid', null) === 1){
                            if(Arr::get($line, 'service_voucher_row_type_id', null) === 3){
                                $pdf->setCellPaddings(4, 0, 0, 0);

                                $pdf->Cell($w*100, 6, Arr::get($line, 'content', null), 0, 1, 'L', 0, '', 1);

                                $pdf->setCellPaddings(1, 0, 1, 0);
                            }else{
                                $pdf->Cell($w*12, 6, Arr::get($line, 'date', null), 0, 0, 'C', !($i%2), '', 1);
                                $pdf->Cell($w*40, 6, Arr::get($line, 'content', null), 0, 0, 'L', !($i%2), '', 1);
                                $pdf->Cell($w*12, 6, Arr::get($line, 'tax_rate_name', null), 0, 0, 'C', !($i%2), '', 1);
                                $pdf->Cell($w*12, 6, Arr::get($line, 'quantity', null), 0, 0, 'R', !($i%2), '', 1);
                                $pdf->Cell($w*12, 6, Arr::get($line, 'unit_price', null), 0, 0, 'R', !($i%2), '', 1);
                                $pdf->Cell($w*12, 6, Arr::get($line, 'total', null), 0, 1, 'R', !($i%2), '', 1);
                            }
                        }else if(Arr::get($line, 'vid', null) === 2){
                            $pdf->Cell($w*12, 6, Arr::get($line, 'date', null), 0, 0, 'C', !($i%2), '', 1);
                            $pdf->Cell($w*64, 6, Arr::get($line, 'content', null), 0, 0, 'L', !($i%2), '', 1);
                            $pdf->Cell($w*24, 6, Arr::get($line, 'amount', null), 0, 1, 'R', !($i%2), '', 1);
                        }else if(Arr::get($line, 'vid', null) === 3){
                            $pdf->Cell($w*12, 6, Arr::get($line, 'date', null), 0, 0, 'C', !($i%2), '', 1);
                            $pdf->Cell($w*64, 6, Arr::get($line, 'content', null), 0, 0, 'L', !($i%2), '', 1);
                            $pdf->Cell($w*24, 6, Arr::get($line, 'amount', null), 0, 1, 'R', !($i%2), '', 1);
                        }

                        break;
                    case 'footer':
                        $pdf->Cell($w*12, 6, null, 0, 0, 'C', !($i%2), '', 1);
                        $pdf->Cell($w*64, 6, Arr::get($line, 'title', null), 0, 0, 'R', !($i%2), '', 1);
                        $pdf->Cell($w*24, 6, Arr::get($line, 'total', null), 0, 1, 'R', !($i%2), '', 1);
                        break;
                    case 'space':
                        $pdf->Cell($w*100, 6, null, 0, 1, 'L', !($i%2), '', 1);
                        break;
                    case 'invoice.footer':
                        $pdf->Cell($w*25, 6, Arr::get($line, 'tax_rate_name', null) . ' 対象', 0, 0, 'R', !($i%2), '', 1);
                        $pdf->Cell($w*25, 6, Arr::get($line, 'total_net_amount', null), 0, 0, 'R', !($i%2), '', 1);
                        $pdf->Cell($w*25, 6, Arr::get($line, 'tax_rate_name', null) . ' 消費税', 0, 0, 'R', !($i%2), '', 1);
                        $pdf->Cell($w*25, 6, Arr::get($line, 'total_tax_amount', null), 0, 1, 'R', !($i%2), '', 1);
                        break;
                    default:
                        $pdf->Cell($w*100, 6, null, 0, 1, 'L', !($i%2), '', 1);
                }
            }
            // 内容ここまで
        }

        $pdf->Output("{$invoice->id}.pdf", 'I');
    }
}
