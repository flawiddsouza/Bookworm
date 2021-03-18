@extends('auth.base')

@section('content')
<form method="POST">
    @csrf
    <div>
        <label>Name<br>
            <input type="text" name="name" required autofocus>
        </label>
    </div>

    <div class="mt-1em">
        <label>Email<br>
            <input type="text" name="email" required>
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

    <div class="mt-1em">
        <button>Register</button>
    </div>

    <div class="mt-1em">
        <a href="{{ route('login') }}">{{ __('Already have an account? Click here.') }}</a>
    </div>
</form>
@endsection
