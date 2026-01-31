<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class Checkout extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tool_id',
        'worker_id',
        'checked_out_at',
        'expected_return_at',
        'returned_at',
        'checked_out_by',
        'returned_by',
        'condition_out',
        'condition_in',
        'checkout_notes',
        'return_notes',
        'is_overdue',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'returned_at' => 'datetime',
        'is_overdue' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tool_id', 'worker_id', 'returned_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }

    public function scopeReturned($query)
    {
        return $query->whereNotNull('returned_at');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('returned_at')
            ->where('expected_return_at', '<', now())
            ->orWhere('is_overdue', true);
    }

    public function scopeForWorker($query, int $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    public function scopeForTool($query, int $toolId)
    {
        return $query->where('tool_id', $toolId);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return is_null($this->returned_at);
    }

    public function getIsReturnedAttribute(): bool
    {
        return !is_null($this->returned_at);
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->returned_at) {
            return false;
        }

        if (!$this->expected_return_at) {
            return false;
        }

        return $this->expected_return_at->isPast();
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->returned_at) {
            return null;
        }

        return $this->checked_out_at->diffForHumans($this->returned_at, true);
    }

    // Methods
    public function checkOverdue(): void
    {
        if ($this->is_overdue_attribute !== $this->is_overdue) {
            $this->update(['is_overdue' => $this->is_overdue_attribute]);
        }
    }
}
