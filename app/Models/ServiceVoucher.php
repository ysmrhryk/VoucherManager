<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ServiceVoucher extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'date',
        'customer_note',
        'internal_note',
        'user_id',
        'client_id', // 請求先

        // 担当者名がいるとか、部署名がいるとかに対応できるようにする
        'extension_client_name',
        'name_suffix_id',

        'total_net_amount',

        'invoice_id',
    ];

    public function bodies()
    {
        return $this->hasMany(ServiceVoucherBody::class);
    }

    public function footers()
    {
        return $this->hasMany(ServiceVoucherFooter::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function name_suffix()
    {
        return $this->belongsTo(NameSuffix::class);
    }

    protected static function booted(): void
    {
        static::updating(function ($service_voucher) {
            if ($service_voucher->getOriginal('invoice_id')) {
                throw new \Exception('集計済みの伝票は更新できません。請求書を取り消して再度お試しください。');
            }
        });

        static::deleting(function ($service_voucher) {
            if ($service_voucher->invoice_id) {
                throw new \Exception('集計済みの伝票は削除できません。請求書を取り消して再度お試しください。');
            }
        });

        // creatingとupdatingに分けたほうがいいかどうか検討中
        static::saving(function (ServiceVoucher $service_voucher) {
            // 担当者名のスナップショット
            $service_voucher->user_name = User::findOrFail($service_voucher->user_id)->name;

            // 得意先のスナップショット
            $client = Client::findOrFail($service_voucher->client_id);

            $service_voucher->client_name = $client->name;
            $service_voucher->client_postal = $client->postal;
            $service_voucher->client_address = $client->address;
            $service_voucher->client_tel = $client->tel;
            $service_voucher->client_fax = $client->fax;

            $settings = Settings::firstOrFail();

            $service_voucher->my_name = $settings->name;
            $service_voucher->my_postal = $settings->postal;
            $service_voucher->my_address = $settings->address;
            $service_voucher->my_tel = $settings->tel;
            $service_voucher->my_fax = $settings->fax;
        });
    }
}
