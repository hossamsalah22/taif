{{-- Server-side validation messages are translated, the browser's built-in ones are not, so forms skip HTML5 validation. --}}
<script>
    (() => {
        const disableBrowserValidation = () => {
            document.querySelectorAll('form:not([novalidate])').forEach((form) => form.setAttribute('novalidate', 'novalidate'));
        };

        document.addEventListener('DOMContentLoaded', () => {
            disableBrowserValidation();

            // Livewire morphing can add new forms or drop the attribute again.
            new MutationObserver(disableBrowserValidation).observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['novalidate'],
            });
        });

        document.addEventListener('livewire:navigated', disableBrowserValidation);
    })();
</script>

@if ($avatarUrl = auth()->user()?->getFilamentAvatarUrl())
    {{-- Show the admin's avatar instead of the generic icon on the profile menu item. --}}
    <style>
        .fi-dropdown-list-item[href*='profile'] .fi-dropdown-list-item-icon,
        .fi-dropdown-list-item[href*='profile'] svg {
            display: none !important;
        }

        .fi-dropdown-list-item[href*='profile'] {
            display: flex !important;
            align-items: center !important;
        }

        .fi-dropdown-list-item[href*='profile']::before {
            content: '';
            display: block;
            width: 24px;
            height: 24px;
            background-image: url('{{ $avatarUrl }}');
            background-size: cover;
            background-position: center;
            border-radius: 50%;
            margin-inline-end: 0.75rem;
            flex-shrink: 0;
        }
    </style>
@endif

<style>
    .fi-logo {
        margin: 0 auto !important;
        display: flex !important;
        justify-content: center !important;
    }
</style>
