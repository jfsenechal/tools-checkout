<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Worker extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function checkouts(): HasMany
    {
        return $this->hasMany(Checkout::class);
    }

    public function activeCheckouts(): HasMany
    {
        return $this->hasMany(Checkout::class)
            ->whereNull('returned_at');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getHasActiveCheckoutsAttribute(): bool
    {
        return $this->activeCheckouts()->exists();
    }

    public function getActiveCheckoutsCountAttribute(): int
    {
        return $this->activeCheckouts()->count();
    }
}
