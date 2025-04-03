<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderInstallmentStatus extends Model
{
    use HasFactory;

    protected $table = 'order_installment_statuses';

    protected $fillable = [
        'status_name_en', 'status_name_ka', 'comment_en', 'comment_ka', 'payment_type_id'
    ];

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }
}
