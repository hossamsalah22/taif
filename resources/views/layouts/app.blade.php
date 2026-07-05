@php
    $currentLocale = $locale ?? session('locale', app()->getLocale());
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" dir="{{ $currentLocale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Page' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Tajawal:wght@300;400;500;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            /* Color Palette */
            --primary: #1F9FDE;
            --primary-dark: #1886bd;
            --secondary: #4EAF50;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --radius: 1rem;
        }

        /* Dark Mode Preferences handled softly or just clean light mode for generic pages unless specified */
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Arabic Override */
        html[dir="rtl"] body {
            font-family: 'Tajawal', sans-serif;
        }

        header {
            background-color: var(--bg-card);
            box-shadow: var(--shadow);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand img {
            height: 3rem;
            width: auto;
        }

        .lang-switch {
            display: flex;
            gap: 1rem;
        }

        .lang-link {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
            background-color: var(--primary);
            color: white;
        }

        main {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .content-card {
            background-color: var(--bg-card);
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            margin-top: 0;
            color: var(--primary-dark);
            font-size: 2rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .prose p {
            margin-bottom: 1rem;
        }

        .prose {
            color: var(--text-main);
        }

        /* Utility for HTML content injected from backend */
        .prose h2 {
            font-size: 1.5rem;
            color: var(--text-main);
            margin-top: 2rem;
        }

        .prose ul,
        .prose ol {
            margin-bottom: 1rem;
            padding-inline-start: 1.5rem;
        }

        .prose li {
            margin-bottom: 0.5rem;
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <header>
        <div class="header-content">
            <a href="/" class="brand">
                <img src="{{ \Illuminate\Support\Facades\Storage::url('clarity_header.svg') }}" alt="Wayekum">
            </a>
            <div class="lang-switch">
                @if ($currentLocale === 'ar')
                    <a href="{{ route('set.language', 'en') }}"
                        class="lang-link {{ $currentLocale === 'en' ? 'active' : '' }}">English</a>
                @else
                    <a href="{{ route('set.language', 'ar') }}"
                        class="lang-link {{ $currentLocale === 'ar' ? 'active' : '' }}">العربية</a>
                @endif
            </div>
        </div>
    </header>

    <main>
        <div class="content-card">
            <h1>{{ $title }}</h1>
            <div class="prose">
                @yield('content')
            </div>
        </div>
    </main>

    <footer>
        {{ $currentLocale === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}

        &copy; {{ $currentLocale === 'ar' ? 'وعيكم' : 'Wayekum' }} {{ date('Y') }}.
    </footer>
</body>

</html>
