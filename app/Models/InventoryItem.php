<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sku', 'price'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function scopeSearch($query, $filters)
    {
        return $query
            ->when($filters['name'] ?? null, fn ($q, $v) =>
                $q->where('name', 'like', "%$v%"))
            ->when($filters['min_price'] ?? null, fn ($q, $v) =>
                $q->where('price', '>=', $v))
            ->when($filters['max_price'] ?? null, fn ($q, $v) =>
                $q->where('price', '<=', $v));
    }
}

