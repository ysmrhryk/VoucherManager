<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Invoice;

class AfterLatestInvoiceDate implements ValidationRule
{
    public function __construct(protected ?int $client_id)
    {
        // 値を受け取りたいだけなので特に書くことはない
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // valueには日付が入る

        if(!$this->client_id) return; // 得意先IDの指定がない場合は何もしない

        $latest_invoice = Invoice::where('client_id', $this->client_id)
        ->latest('date')
        ->value('date'); // 最後に発行された請求書の日付を出力

        if($latest_invoice && $value <= $latest_invoice){
            $fail("集計済みの日付です。{$latest_invoice}以前の日付を指定するか、その日付までの請求書を取り消して再度お試しください。");   
        }
    }
}
