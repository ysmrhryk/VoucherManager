<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'name', // 屋号
        'postal', // 郵便番号
        'address', // 住所
        'tel', // 電話番号
        'fax', // FAX番号
        'bank_name', // 銀行名
        'branch_name', // 支店名
        'account_type', // 預金種目
        'account_number', // 口座番号
        'account_holder', // 口座名義
    ];
}
