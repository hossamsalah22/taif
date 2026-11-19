<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Translatable\HasTranslations;

class LearningLesson extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'learning_goal_id',
        'name',
        'reward_id',
        'is_locked',
        'display_priority',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function goal()
    {
        return $this->belongsTo(LearningGoal::class, 'learning_goal_id');
    }

    public function exercises()
    {
        return $this->hasMany(LearningExercise::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Copies this lesson with its exercises (and their media) into another goal. The original stays in its goal.
     */
    public function copyToGoal(LearningGoal $goal, int $displayPriority): self
    {
        return DB::transaction(function () use ($goal, $displayPriority): self {
            $copy = $this->replicate(['exercises_count']);
            $copy->learning_goal_id = $goal->id;
            $copy->display_priority = $displayPriority;
            $copy->save();

            foreach ($this->exercises()->with('media')->get() as $exercise) {
                $exerciseCopy = $exercise->replicate();
                $exerciseCopy->learning_lesson_id = $copy->id;
                $exerciseCopy->save();

                foreach ($exercise->media as $media) {
                    $media->copy($exerciseCopy, $media->collection_name, $media->disk);
                }
            }

            return $copy;
        });
    }
}
