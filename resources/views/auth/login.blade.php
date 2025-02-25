@extends('layouts.app')

@section('content')
    <div class="form-box">
        <h2 class="form-title">Login to your ProjeX Account</h2>
        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <label for="username_email">Username or E-mail</label>
            <input id="username_email" type="text" name="username-email" value="{{ old('username-email') }}" required
                autofocus>
            @if ($errors->has('username-email'))
                <span class="form-error">{{ $errors->first('username-email') }}</span>
            @endif

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
            @if ($errors->has('password'))
                <span class="form-error">{{ $errors->first('password') }}</span>
            @endif

            <label class="form-checkbox">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
            </label>

            <button type="submit">Login</button>
            <a class="btn" href="{{ route('register') }}">Sign Up</a>
            <a href="{{ route('forgot.password.show') }}">Forgot your password?</a>

            @if (session('success'))
                <p class="form-success">{{ session('success') }}</p>
            @endif
        </form>
    </div>
@endsection
