<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;
    protected $table = 'warranties';
    protected $fillable = ['warranty_period'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
