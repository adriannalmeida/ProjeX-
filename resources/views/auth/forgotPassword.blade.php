@extends('layouts.app')

@section('content')
    <div class="form-box">
        <h2 class="form-title">Request a password reset email</h2>
        <form method="POST" action="{{ url('/forgot-password') }}">
            @csrf

            <label for="username-email">Username or E-mail</label>
            @if (old('username-email'))
                <input id="username-email" type="text" name="username-email" value="{{ old('username-email') }}" required
                    autofocus>
            @elseif(!empty($email))
                <input id="username-email" type="text" name="username-email" value="{{ $email }}" required
                    autofocus>
            @else
                <input id="username-email" type="text" name="username-email" value="{{ old('username-email') }}" required
                    autofocus>
            @endif
            @if ($errors->has('username-email'))
                <span class="form-error">{{ $errors->first('username-email') }}</span>
            @endif

            <button type="submit">Send email</button>
            @if (Auth::user())
                <a class="btn" href="{{ route('account.edit') }}">Go back</a>
            @else
                <a class="btn" href="{{ route('login') }}">Go back</a>
            @endif

            @if (session('success'))
                <p class="form-success">{{ session('success') }}</p>
            @endif
        </form>
    </div>
@endsection
