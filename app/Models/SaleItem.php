<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'cost_price',
        'sell_price',
        'quantity',
        'subtotal_cost',
        'subtotal_revenue',
        'subtotal_profit',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'subtotal_cost' => 'decimal:2',
        'subtotal_revenue' => 'decimal:2',
        'subtotal_profit' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
