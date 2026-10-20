<?php

namespace App\Models;

use Database\Factories\SupportTicketAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'support_ticket_id',
    'support_ticket_reply_id',
    'path',
    'original_name',
    'mime_type',
    'size',
])]
class SupportTicketAttachment extends Model
{
    /** @use HasFactory<SupportTicketAttachmentFactory> */
    use HasFactory;

    public const DISK = 'public';

    public const MAX_FILES = 5;

    /** Maximum size of a single file in kilobytes (10 MB). */
    public const MAX_FILE_SIZE_KB = 10240;

    /** @var array<int, string> */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

    protected static function booted(): void
    {
        static::deleted(function (SupportTicketAttachment $attachment): void {
            Storage::disk(self::DISK)->delete($attachment->path);
        });
    }

    /**
     * Store an uploaded file and record it against the given ticket (and optional reply).
     */
    public static function storeFor(UploadedFile $file, SupportTicket $ticket, ?SupportTicketReply $reply = null): self
    {
        return self::create([
            'support_ticket_id' => $ticket->id,
            'support_ticket_reply_id' => $reply?->id,
            'path' => $file->store("support-tickets/{$ticket->id}", self::DISK),
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Validation rules for an array of uploaded attachments.
     *
     * @return array<string, array<int, string>>
     */
    public static function validationRules(string $field = 'attachments'): array
    {
        return [
            $field => ['nullable', 'array', 'max:'.self::MAX_FILES],
            "{$field}.*" => ['file', 'extensions:'.implode(',', self::ALLOWED_EXTENSIONS), 'max:'.self::MAX_FILE_SIZE_KB],
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(SupportTicketReply::class, 'support_ticket_reply_id');
    }

    public function url(): string
    {
        return Storage::disk(self::DISK)->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * @return array{id: int, name: string, url: string, mime_type: string, size: int, is_image: bool}
     */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->original_name,
            'url' => $this->url(),
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'is_image' => $this->isImage(),
        ];
    }
}
