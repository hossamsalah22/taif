<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\GenderEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Guarded(['id'])]
#[Hidden(['remember_token', 'otp', 'media'])]
#[Appends(['image'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, InteractsWithMedia, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => GenderEnum::class,
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'receive_notifications' => 'boolean',
            'otp_expires_at' => 'datetime',
        ];
    }

    protected $with = ['media'];

    public function routeNotificationForFcm(): array|string
    {
        if (! $this->receive_notifications) {
            return [];
        }

        return $this->getDeviceTokens();
    }

    public function getDeviceTokens(): array
    {
        return $this->firebaseTokens()->pluck('token')->toArray();
    }

    public function firebaseTokens(): MorphMany
    {
        return $this->morphMany(DeviceToken::class, 'tokenable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('users')->singleFile();
    }

    public function getImageAttribute(): string
    {
        return $this->getFirstMediaUrl('users', 'thumb');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'iso');
    }
}
