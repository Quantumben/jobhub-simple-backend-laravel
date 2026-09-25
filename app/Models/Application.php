<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'company',
        'location',
        'job_type',
        'work_mode',
        'salary_min',
        'salary_max',
        'description',
        'requirements',
        'application_url',
        'image',
        'image_public_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'salary_min' => 'float',
            'salary_max' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }
}
