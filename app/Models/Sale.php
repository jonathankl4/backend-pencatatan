<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sale_code',
        'total_cost',
        'total_revenue',
        'gross_profit',
        'notes',
        'sale_date',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
