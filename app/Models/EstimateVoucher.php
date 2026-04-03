<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EstimateVoucher extends Model
{
    use HasUuids;

    protected $fillable = [
        'date',
        'expiry_date',
        'customer_note',
        'internal_note',
        'user_id',
        'client_id', // 請求先

        // 担当者名がいるとか、部署名がいるとかに対応できるようにする
        'extension_client_name',
        'name_suffix_id',

        'total_net_amount',
    ];

    public function bodies()
    {
        return $this->hasMany(EstimateVoucherBody::class);
    }

    public function footers()
    {
        return $this->hasMany(EstimateVoucherFooter::class);
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
        static::saving(function (EstimateVoucher $estimate_voucher) {
            // 担当者名のスナップショット
            $estimate_voucher->user_name = User::findOrFail($estimate_voucher->user_id)->name;

            // 得意先のスナップショット
            $client = Client::findOrFail($estimate_voucher->client_id);

            $estimate_voucher->client_name = $client->name;
            $estimate_voucher->client_postal = $client->postal;
            $estimate_voucher->client_address = $client->address;
            $estimate_voucher->client_tel = $client->tel;
            $estimate_voucher->client_fax = $client->fax;

            $settings = Settings::firstOrFail();

            $estimate_voucher->my_name = $settings->name;
            $estimate_voucher->my_postal = $settings->postal;
            $estimate_voucher->my_address = $settings->address;
            $estimate_voucher->my_tel = $settings->tel;
            $estimate_voucher->my_fax = $settings->fax;
        });
    }
}
