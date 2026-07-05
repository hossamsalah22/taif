<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['tokenable_type', 'tokenable_id', 'token', 'device_type'])]
#[Table(timestamps: false)]
class DeviceToken extends Model
{
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
