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
    <div id="app">
        <calendar :user='@json(Auth::user())' />
    </div>
</body>

</html>
