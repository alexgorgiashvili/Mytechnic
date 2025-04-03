<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    
    protected $fillable = ['method_name'];

    public function orderInstallments()
    {
        return $this->hasMany(OrderInstallment::class, 'payment_type_id');
    }

    public function orderInstallmentStatuses()
    {
        return $this->hasMany(OrderInstallmentStatus::class, 'payment_type_id');
    }
}
