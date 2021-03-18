@extends('auth.base')

@section('content')
<form method="POST">
    @csrf
    <div>
        <label>Email<br>
            <input type="text" name="email" required autofocus>
        </label>
    </div>

    <div class="mt-1em">
        <label>Password<br>
            <input type="password" name="password" required>
        </label>
    </div>

    <div class="mt-1em">
        <button>Login</button> <a href="{{ route('password.request') }}" class="ml-1em">{{ __('Forgot your password?') }}</a>
    </div>

    <div class="mt-1em">
        <a href="{{ route('register') }}">{{ __('New user? Register here.') }}</a>
    </div>
</form>
@endsection
