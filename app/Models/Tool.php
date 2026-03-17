<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Tool extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'qr_code',
        'category',
        'description',
        'status',
        'manufacturer',
        'model',
    ];

    protected $casts = [
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'category'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function checkouts(): HasMany
    {
        return $this->hasMany(Checkout::class);
    }

    public function currentCheckout(): HasOne
    {
        return $this->hasOne(Checkout::class)
            ->whereNull('returned_at')
            ->latestOfMany();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('status', 'checked_out');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Accessors & Mutators
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }

    public function getIsCheckedOutAttribute(): bool
    {
        return $this->status === 'checked_out';
    }

    public function getQrCodeUrlAttribute(): string
    {
        return $this->qr_code
            ? asset('storage/qrcodes/'.$this->qr_code)
            : '';
    }

    // Methods
    public function markAsCheckedOut(): void
    {
        $this->update(['status' => 'checked_out']);
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => 'available']);
    }

    public function markAsInMaintenance(): void
    {
        $this->update(['status' => 'maintenance']);
    }
}
