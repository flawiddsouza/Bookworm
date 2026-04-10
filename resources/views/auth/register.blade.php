@extends('auth.base')

@section('content')
<form method="POST">
    @csrf
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" required autofocus>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="text" name="email" required>
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>
    </div>

    <div class="mb-1em">
        <button>Register</button>
    </div>

    <div>
        <a href="{{ route('login') }}">{{ __('Already have an account? Click here.') }}</a>
    </div>
</form>
@endsection
