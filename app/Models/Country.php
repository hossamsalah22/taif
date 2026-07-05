<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected array $translatable = ['name'];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
