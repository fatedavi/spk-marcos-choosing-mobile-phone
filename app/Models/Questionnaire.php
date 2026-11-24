<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Questionnaire extends Model
{
    use HasFactory;

    protected $table = 'questionnaire';

    protected $fillable = [
        'criteria_id',
        'n_ss',
        'n_s',
        'n_c',
        'n_ts',
        'n_sts',
    ];

    protected function casts(): array
    {
        return [
            'n_ss' => 'integer',
            'n_s' => 'integer',
            'n_c' => 'integer',
            'n_ts' => 'integer',
            'n_sts' => 'integer',
        ];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criteria_id');
    }
}
