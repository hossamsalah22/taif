<div style="display: flex; flex-direction: column; gap: 1rem;">
    @foreach ($record->replies()->with(['admin', 'user'])->latest()->get() as $reply)
        <div style="padding: 1rem; border-radius: 0.5rem; background-color: rgba(156, 163, 175, 0.1); border: 1px solid rgba(156, 163, 175, 0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-weight: bold; font-size: 0.875rem;">
                    {{ $reply->admin_id ? ($reply->admin->name ?? 'System') : ($reply->user->name ?? 'Parent') }}
                </span>
                <span style="font-size: 0.75rem; opacity: 0.7;">{{ $reply->created_at->format('Y-m-d H:i') }}</span>
            </div>
            <div style="font-size: 0.875rem; white-space: pre-wrap; line-height: 1.5;">{{ $reply->reply_text }}</div>
        </div>
    @endforeach
    
    @if($record->replies()->count() === 0)
        <div style="font-size: 0.875rem; opacity: 0.5; font-style: italic;">No replies yet.</div>
    @endif
</div>
