<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RefundVoucher extends Model
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
        return $this->hasMany(RefundVoucherBody::class);
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
        static::updating(function ($refund_voucher) {
            if ($refund_voucher->getOriginal('invoice_id')) {
                throw new \Exception('集計済みの伝票は更新できません。請求書を取り消して再度お試しください。');
            }
        });

        static::deleting(function ($refund_voucher) {
            if ($refund_voucher->invoice_id) {
                throw new \Exception('集計済みの伝票は削除できません。請求書を取り消して再度お試しください。');
            }
        });

        static::saving(function (RefundVoucher $refund_voucher) {
            // 得意先のスナップショット
            $client = Client::findOrFail($refund_voucher->client_id);

            $refund_voucher->client_name = $client->name;
            $refund_voucher->client_postal = $client->postal;
            $refund_voucher->client_address = $client->address;
            $refund_voucher->client_tel = $client->tel;
            $refund_voucher->client_fax = $client->fax;

            $settings = Settings::firstOrFail();

            $refund_voucher->my_name = $settings->name;
            $refund_voucher->my_postal = $settings->postal;
            $refund_voucher->my_address = $settings->address;
            $refund_voucher->my_tel = $settings->tel;
            $refund_voucher->my_fax = $settings->fax;
        });
    }
}
