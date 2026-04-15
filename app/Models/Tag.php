<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(TagFactory::class)]
final class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }
}
