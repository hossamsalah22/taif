<?php

namespace App\Models;

use App\Enums\VerificationTypeEnum;
use Database\Factories\UserVerificationFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class UserVerification extends Model
{
    /** @use HasFactory<UserVerificationFactory> */
    use HasFactory;

    protected $casts = [
        'type' => VerificationTypeEnum::class,
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
