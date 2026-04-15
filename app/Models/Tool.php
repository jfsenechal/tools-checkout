<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusToolEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Tool extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'qr_code',
        'category_id',
        'description',
        'status',
        'manufacturer',
        'model',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

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
        return $query->where('status', StatusToolEnum::Available);
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('status', StatusToolEnum::CheckedOut);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Accessors & Mutators
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === StatusToolEnum::Available;
    }

    public function getIsCheckedOutAttribute(): bool
    {
        return $this->status === StatusToolEnum::CheckedOut;
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
        $this->update(['status' => StatusToolEnum::CheckedOut]);
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => StatusToolEnum::Available]);
    }

    public function markAsInMaintenance(): void
    {
        $this->update(['status' => StatusToolEnum::Maintenance]);
    }

    protected function casts(): array
    {
        return [
            'status' => StatusToolEnum::class,
        ];
    }
}
