<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'website',
        'location',
        'description',
        'logo',
        'logo_public_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(
            Application::class
        );
    }
}
