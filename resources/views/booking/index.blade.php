<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Букинг | Тепло на белой</title>
    <meta name="author" content="Andrew Korobkov">
    <meta name="robots" content="noindex, nofollow">
    <meta name="google" content="noindex, nofollow">
    <meta name="bing" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/images/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/images/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/images/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Тепло на белой" />
    <link rel="manifest" href="/images/favicon/site.webmanifest" />
    @vite('resources/js/app.js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    @if (Auth::check())
        <div class="bg-gradient-to-b from-blue-500 to-blue-600 py-2 mx-auto max-w-7xl">
            <div class="mx-auto max-w-7xl px-4 md:px-6 flex justify-between items-center">
                <p class="text-white text-sm">Привет, {{ Auth::user()->name }}</p>
                <a href="{{ route('logout') }}" class=" text-sm text-white">Выйти</a>
            </div>
        </div>
    @endif
    <div id="app">
        <calendar />
    </div>
</body>

</html>
