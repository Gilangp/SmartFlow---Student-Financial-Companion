<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SmartFlow')</title>

    <!-- Tailwind CSS Framework -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js for Reactive Components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    @include('layouts.navbar')

    <main class="max-w-md mx-auto px-4 py-6 space-y-6">
        @yield('content')
    </main>

</body>
</html>
