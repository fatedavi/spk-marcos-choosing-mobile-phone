<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubCriterion extends Model
{
    use HasFactory;

    protected $table = 'sub_criteria';

    protected $fillable = [
        'criteria_id',
        'score',
        'min_value',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'min_value' => 'decimal:2',
        ];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criteria_id');
    }
}
