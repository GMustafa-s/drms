<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'product_type_id'
    ];
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    public function productType()
{
    return $this->belongsTo(ProductType::class);
}
}
