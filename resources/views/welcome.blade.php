<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Articles</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <!-- Header -->
    <header class="w-full border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Articles & Comments</h1>


            <nav class="flex items-center gap-4">
                <!-- Authenticated user section (hidden by default) -->
                <div id="auth-section" class="hidden items-center gap-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Welcome, <span id="user-name"></span></span>
                    <button onclick="logout()"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 rounded-md text-sm transition-colors">
                        Logout
                    </button>
                </div>

                <!-- Login form section (shown by default) -->
                <div id="login-section" class="flex gap-2">
                    <input type="email" id="email" placeholder="Email" value="john@example.com"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700">
                    <input type="password" id="password" placeholder="Password" value="password"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700">
                    <button onclick="login()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm transition-colors">
                        Login
                    </button>
                </div>
            </nav>

        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-6 py-8">
        <div id="loading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Loading articles...</p>
        </div>

        <div id="articles-container" class="hidden space-y-6">
            <!-- Articles will be loaded here -->
        </div>
    </main>

    <script defer src="{{ asset('js/endpoints.js') }}"></script>
    <script defer src="{{ asset('js/auth.js') }}"></script>
    <script defer src="{{ asset('js/app.js') }}"></script>
</body>

</html>
