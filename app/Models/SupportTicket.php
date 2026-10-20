<?php

namespace App\Models;

use App\Enums\SupportTicketStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id',
    'reference_number',
    'title',
    'description',
    'status',
    'assigned_admin_id',
]
)]
class SupportTicket extends Model
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'status' => SupportTicketStatusEnum::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class);
    }

    /**
     * Files attached when the ticket was opened (not those attached to replies).
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class)->whereNull('support_ticket_reply_id');
    }
}
