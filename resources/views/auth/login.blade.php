@extends('auth.base')

@section('content')
<form method="POST">
    @csrf
    <div class="form-group">
        <label>Email</label>
        <input type="text" name="email" required autofocus>
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <div class="mb-1em">
        <label>
            <input type="checkbox" name="remember"> Keep me logged in forever
        </label>
    </div>

    <div class="mb-1em">
        <button>Login</button> <a href="{{ route('password.request') }}" class="ml-1em">{{ __('Forgot your password?') }}</a>
    </div>

    <div>
        <a href="{{ route('register') }}">{{ __('New user? Register here.') }}</a>
    </div>
</form>
@endsection
