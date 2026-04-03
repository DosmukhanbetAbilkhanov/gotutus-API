<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tanys — Find Your Perfect Hangout Partner')</title>
    <meta name="description" content="@yield('meta_description', 'Tanys helps you find people to hang out with. Create meetups, join activities, and meet new friends in your city.')">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', 'Tanys — Find Your Perfect Hangout Partner')">
    <meta property="og:description" content="@yield('og_description', 'Create meetups, join activities, and meet new friends in your city.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $lang ?? 'en' }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Righteous';
            src: url('{{ asset('fonts/Righteous/Righteous-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
    </style>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#6366F1',
                            light: '#EEF2FF',
                            dark: '#4F46E5',
                        },
                        secondary: {
                            DEFAULT: '#10B981',
                            light: '#D1FAE5',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        righteous: ['Righteous', 'cursive'],
                    },
                },
            },
        }
    </script>

    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-900 bg-white">
    @yield('content')

    @stack('scripts')
</body>
</html>
