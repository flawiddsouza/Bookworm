<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bookworm</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite('resources/scripts/main.js')
    <script>
        csrfToken = @json(csrf_token())
    </script>
</head>
<body>
    <div id="app"></div>
</body>
</html>
