<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $fillable = [
        'type'
    ];
    /** @use HasFactory<\Database\Factories\ProductTypeFactory> */
    use HasFactory;

    public function products()
{
    return $this->hasMany(Product::class);
}
}
