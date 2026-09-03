<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;

class ClinicalProgressReport extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['title', 'smart_parental_advice'];

    protected $fillable = [
        'child_id',
        'learning_plan_id',
        'reportable_id',
        'reportable_type',
        'title',
        'body',
        'smart_parental_advice',
        'strengths',
        'improvements',
        'is_visible_to_parent',
    ];

    protected $casts = [
        'strengths' => 'array',
        'improvements' => 'array',
        'is_visible_to_parent' => 'boolean',
    ];

    public function reportable()
    {
        return $this->morphTo();
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function plan()
    {
        return $this->belongsTo(LearningPlan::class, 'learning_plan_id');
    }
}
