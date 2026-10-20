@if ($attachments->isNotEmpty())
    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
        @foreach ($attachments as $attachment)
            @if ($attachment->isImage())
                <a href="{{ $attachment->url() }}" target="_blank" rel="noopener" title="{{ $attachment->original_name }}">
                    <img src="{{ $attachment->url() }}" alt="{{ $attachment->original_name }}"
                        style="width: 6rem; height: 6rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.3);">
                </a>
            @else
                <a href="{{ $attachment->url() }}" target="_blank" rel="noopener"
                    style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; padding: 0.4rem 0.6rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.3); text-decoration: underline;">
                    <x-filament::icon icon="heroicon-o-document" style="width: 1rem; height: 1rem;" />
                    {{ $attachment->original_name }}
                </a>
            @endif
        @endforeach
    </div>
@endif
