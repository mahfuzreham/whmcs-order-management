<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalPayment extends Model
{
    protected $fillable = [
        'client_id','invoice_id','gateway','amount','currency','status',
        'payment_id','transaction_id','reference','payload','completed_at',
        'refund_status','refund_amount','refund_id','refund_payload','refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'payload' => 'array',
        'refund_payload' => 'array',
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];
}
