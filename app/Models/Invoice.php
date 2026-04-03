<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{ BelongsTo, HasMany };

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'date',

        'client_id',
        'client_code',
        'client_name',
        'client_postal',
        'client_address',
        'client_tel',
        'client_fax',

        'name_suffix_id',
        'name_suffix_value',

        'previous_invoice_amount',
        'total_receipt_amount',
        'total_refund_amount',
        'carried_forward_amount',
        
        'total_net_amount',
        'total_tax_amount',
        'total_gross_amount',
        'current_invoice_amount',

        'my_name',
        'my_postal',
        'my_address',
        'my_tel',
        'my_fax',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function footers(): HasMany
    {
        return $this->hasMany(InvoiceFooter::class);
    }

    protected static function booted(): void
    {
        static::deleting(function ($invoice) {
            $is_newer_invoice_exists = Invoice::where('date', '>', $invoice->date)->where('client_id', $invoice->client_id)->exists();

            if($is_newer_invoice_exists){
                throw new \Exception('より新しい請求書が存在します。この請求書を取り消すことはできません。');
            }
        });
        
        static::addGlobalScope('order_by_date_desc', function (Builder $builder) {
            $builder->orderBy('date', 'desc');
        });
    }
}
