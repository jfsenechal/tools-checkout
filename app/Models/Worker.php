<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Worker extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'badge_number',
        'email',
        'phone',
        'department',
        'position',
        'status',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'badge_number', 'status', 'department'])
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

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
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
