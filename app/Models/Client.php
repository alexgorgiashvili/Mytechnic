<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'last_name', 'phone', 'personal_number'
    ];

    public function orders()
    {
        return $this->hasMany(OrderInstallment::class);
    }
}
