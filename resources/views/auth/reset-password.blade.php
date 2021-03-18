@extends('auth.base')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
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
        <label>Confirm Password<br>
            <input type="password" name="password_confirmation" required>
        </label>
    </div>

    <input type="hidden" name="token" value="{{ request()->route('token') }}">

    <div class="mt-1em">
        <button>Reset Password</button>
    </div>
</form>
@endsection
