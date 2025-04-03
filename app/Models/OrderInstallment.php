<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code', 'check_hash', 'client_full_name', 'mobile', 
        'payment_type_id', 'fact_address', 'status_id', 'product_ids'
    ];

    protected $casts = [
        'product_ids' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderInstallmentStatus::class, 'status_id');
    }
}

