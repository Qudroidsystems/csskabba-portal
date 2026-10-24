<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceiptLine extends Model
{
    public $timestamps = false;

    protected $fillable = ['stock_receipt_id', 'item_id', 'purchase_order_line_id', 'quantity', 'unit_cost'];

    protected $casts = ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:4'];

    public function item(): BelongsTo { return $this->belongsTo(StockItem::class, 'item_id'); }
}
