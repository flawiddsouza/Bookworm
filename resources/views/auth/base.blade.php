<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bookworm</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="p-1em">
    @if($errors->any())
        <div style="border: 1px solid red; padding: 0.5em; border-radius: 5px" class="mb-1em">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('status'))
        <div class="mb-1em" style="border: 1px solid green; padding: 0.5em; border-radius: 5px">
            {{ session('status') }}
        </div>
    @endif
    @yield('content')
</body>
</html>
