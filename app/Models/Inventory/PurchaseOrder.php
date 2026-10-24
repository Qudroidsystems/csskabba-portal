<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    public const STATUSES = [
        'draft'     => ['Draft', 'st-muted'],
        'ordered'   => ['Ordered', 'st-info'],
        'partial'   => ['Part received', 'st-warning'],
        'received'  => ['Received', 'st-success'],
        'cancelled' => ['Cancelled', 'st-danger'],
    ];

    protected $fillable = ['supplier_id', 'location_id', 'status', 'expected_on', 'notes', 'total', 'created_by'];

    protected $casts = [
        'expected_on'  => 'date',
        'total'        => 'decimal:2',
        'ordered_at'   => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class, 'location_id'); }
    public function lines(): HasMany { return $this->hasMany(PurchaseOrderLine::class); }
    public function receipts(): HasMany { return $this->hasMany(StockReceipt::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function orderedBy(): BelongsTo { return $this->belongsTo(User::class, 'ordered_by'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status][0] ?? ucfirst($this->status); }
    public function getStatusPillAttribute(): string { return self::STATUSES[$this->status][1] ?? 'st-muted'; }

    public function canReceive(): bool { return in_array($this->status, ['ordered', 'partial'], true); }
    public function isEditable(): bool { return $this->status === 'draft'; }
}
