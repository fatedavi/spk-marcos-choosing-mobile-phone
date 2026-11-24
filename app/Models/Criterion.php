<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Criterion extends Model
{
    use HasFactory;

    protected $table = 'criteria';

    protected $fillable = [
        'code',
        'name',
        'unit',
        'type',
        'direction',
        'default_weight',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_weight' => 'decimal:8',
            'sort_order' => 'integer',
        ];
    }

    public function subCriteria(): HasMany
    {
        return $this->hasMany(SubCriterion::class, 'criteria_id')->orderByDesc('min_value');
    }

    public function questionnaire(): HasOne
    {
        return $this->hasOne(Questionnaire::class, 'criteria_id');
    }

    public function alternativeValues(): HasMany
    {
        return $this->hasMany(AlternativeValue::class, 'criteria_id');
    }
}
