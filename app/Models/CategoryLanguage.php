<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryLanguage extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',  // Allow mass assignment for category_id
        'lang',         // Assuming 'lang' might also be mass assigned
        'title',        // Assuming 'title' might also be mass assigned
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
