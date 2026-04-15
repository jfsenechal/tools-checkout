<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CategoryFactory::class)]
final class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }
}
