@extends('auth.base')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <div class="form-group">
        <label>Email</label>
        <input type="text" name="email" required autofocus>
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>
    </div>

    <input type="hidden" name="token" value="{{ request()->route('token') }}">

    <div class="mb-1em">
        <button>Reset Password</button>
    </div>
</form>
@endsection
