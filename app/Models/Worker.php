<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkerFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[UseFactory(WorkerFactory::class)]
final class Worker extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'qr_code',
        'email',
        'phone',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name'])
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

    // Accessors
    public function getQrCodeUrlAttribute(): string
    {
        return $this->qr_code
            ? asset('storage/qrcodes/'.$this->qr_code)
            : '';
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
