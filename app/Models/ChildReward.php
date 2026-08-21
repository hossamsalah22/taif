<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildReward extends Model
{
    protected $fillable = ['child_id', 'reward_id'];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
