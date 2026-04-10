@extends('auth.base')

@section('content')
<form method="POST">
    @csrf
    <div class="form-group">
        <label>Email</label>
        <input type="text" name="email" required autofocus>
    </div>

    <div class="mb-1em">
        <button>Send Mail to Reset Password</button>
    </div>

    <div>
        <a href="{{ route('login') }}">{{ __('Go back to login? Click here.') }}</a>
    </div>
</form>
@endsection
