<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReceiptVoucher extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'date',
        'internal_note',
        'client_id',

        'total_amount',

        'invoice_id',
    ];

    public function bodies()
    {
        return $this->hasMany(ReceiptVoucherBody::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::updating(function ($receipt_voucher) {
            if ($receipt_voucher->getOriginal('invoice_id')) {
                throw new \Exception('集計済みの伝票は更新できません。請求書を取り消して再度お試しください。');
            }
        });

        static::deleting(function ($receipt_voucher) {
            if ($receipt_voucher->invoice_id) {
                throw new \Exception('集計済みの伝票は削除できません。請求書を取り消して再度お試しください。');
            }
        });
        
        static::saving(function (ReceiptVoucher $receipt_voucher) {
            // 得意先のスナップショット
            $client = Client::findOrFail($receipt_voucher->client_id);

            $receipt_voucher->client_name = $client->name;
            $receipt_voucher->client_postal = $client->postal;
            $receipt_voucher->client_address = $client->address;
            $receipt_voucher->client_tel = $client->tel;
            $receipt_voucher->client_fax = $client->fax;

            $settings = Settings::firstOrFail();

            $receipt_voucher->my_name = $settings->name;
            $receipt_voucher->my_postal = $settings->postal;
            $receipt_voucher->my_address = $settings->address;
            $receipt_voucher->my_tel = $settings->tel;
            $receipt_voucher->my_fax = $settings->fax;
        });
    }
}
