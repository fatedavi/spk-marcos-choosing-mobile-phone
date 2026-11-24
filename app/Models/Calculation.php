<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calculation extends Model
{
    use HasFactory;

    protected $table = 'calculations';

    protected $fillable = [
        'user_id',
        'weights',
        'price_mode',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'weights' => 'array',
            'result' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
