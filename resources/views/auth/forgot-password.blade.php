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
        <button>Send Mail to Reset Password</button>
    </div>

    <div class="mt-1em">
        <a href="{{ route('login') }}">{{ __('Go back to login? Click here.') }}</a>
    </div>
</form>
@endsection
