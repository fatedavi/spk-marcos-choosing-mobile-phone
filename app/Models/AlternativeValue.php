<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternativeValue extends Model
{
    use HasFactory;

    protected $table = 'alternative_values';

    protected $fillable = [
        'alternative_id',
        'criteria_id',
        'raw_value',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'raw_value' => 'decimal:2',
            'score' => 'integer',
        ];
    }

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(Alternative::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criteria_id');
    }
}
