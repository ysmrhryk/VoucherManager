<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RequestLog extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'path',
        'payload',
        'ip_address',
        'user_agent',
        'status_code',
        'processing_time',
        'referer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
