<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;

    const DEFAULT_PER_PAGE = 15;

    protected $fillable = ['warehouse_id', 'inventory_item_id', 'quantity'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['warehouse_id'] ?? null, fn ($q, $v) =>
                $q->where('warehouse_id', $v))

            ->when(
                isset($filters['item_name']) ||
                isset($filters['min_price']) ||
                isset($filters['max_price']),
                function ($q) use ($filters) {
                    $q->whereHas('item', function ($iq) use ($filters) {
    
                        if (!empty($filters['item_name'])) {
                            $iq->where('name', 'like', "%{$filters['item_name']}%");
                        }
    
                        if (!empty($filters['min_price'])) {
                            $iq->where('price', '>=', $filters['min_price']);
                        }
    
                        if (!empty($filters['max_price'])) {
                            $iq->where('price', '<=', $filters['max_price']);
                        }
                    });
                }
            )

            ->when($filters['min_quantity'] ?? null, fn ($q, $v) =>
                $q->where('quantity', '>=', $v))

            ->when($filters['max_quantity'] ?? null, fn ($q, $v) =>
                $q->where('quantity', '<=', $v));
    }
}
