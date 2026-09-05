<div style="display: flex; flex-direction: column; gap: 1rem;">
    <!-- Chat messages area -->
    <div id="chat-container" style="display: flex; flex-direction: column; gap: 1rem; max-height: 500px; overflow-y: auto; padding-inline-end: 0.5rem;" class="custom-scrollbar">
        @foreach ($replies as $reply)
            @php
                $isAdmin = $reply->admin_id !== null;
            @endphp
            <div style="display: flex; justify-content: {{ $isAdmin ? 'flex-start' : 'flex-end' }}; width: 100%;">
                <div style="
                    max-width: 80%; 
                    padding: 0.75rem; 
                    border-radius: 0.75rem; 
                    border-bottom-{{ $isAdmin ? 'right' : 'left' }}-radius: 0;
                    background-color: {{ $isAdmin ? 'rgba(var(--primary-500), 0.1)' : 'rgba(156, 163, 175, 0.1)' }}; 
                    border: 1px solid {{ $isAdmin ? 'rgba(var(--primary-500), 0.2)' : 'rgba(156, 163, 175, 0.2)' }};
                ">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; margin-bottom: 0.25rem;">
                        <span style="font-weight: bold; font-size: 0.875rem; color: {{ $isAdmin ? 'rgb(var(--primary-600))' : 'inherit' }};">
                            {{ $isAdmin ? ($reply->admin->name ?? 'System') : ($reply->user->name ?? __('Parent Name')) }}
                        </span>
                        <span style="font-size: 0.7rem; opacity: 0.6; white-space: nowrap;">
                            {{ $reply->created_at->format('Y-m-d H:i') }}
                        </span>
                    </div>
                    <div style="font-size: 0.875rem; white-space: pre-wrap; line-height: 1.5; color: inherit;">
                        {{ $reply->reply_text }}
                    </div>
                </div>
            </div>
        @endforeach

        @if ($replies->isEmpty())
            <div style="text-align: center; padding: 1.5rem 0; font-size: 0.875rem; opacity: 0.5; font-style: italic;">
                {{ __('No replies yet.') }}
            </div>
        @endif
    </div>

    <!-- Chat input area -->
    @if ($ticket->status !== \App\Enums\SupportTicketStatusEnum::CLOSED)
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(156, 163, 175, 0.2);">
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <textarea wire:model="replyText" rows="3" placeholder="{{ __('Reply Composition Box') }}..."
                    style="width: 100%; border-radius: 0.5rem; padding: 0.75rem; border: 1px solid rgba(156, 163, 175, 0.3); background-color: transparent; color: inherit; font-family: inherit; resize: vertical;"
                    ></textarea>
                <div style="display: flex; justify-content: flex-end;">
                    <x-filament::button type="button" wire:click="sendReply" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendReply">
                            {{ __('Send Reply') }}
                        </span>
                        <span wire:loading wire:target="sendReply">
                            {{ __('Sending...') }}
                        </span>
                    </x-filament::button>
                </div>
            </div>
        </div>
    @else
        <div style="margin-top: 1rem; padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); font-size: 0.875rem; text-align: center;">
            {{ __('This ticket is closed and cannot be replied to.') }}
        </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatContainer = document.getElementById('chat-container');
            if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

            Livewire.on('reply-sent', () => {
                setTimeout(() => {
                    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 100);
            });
        });
    </script>
</div>
