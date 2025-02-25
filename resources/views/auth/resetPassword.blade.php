@extends('layouts.app')

@section('content')
    <div class="form-box">
        <h2 class="form-title">Reset your ProjeX account password</h2>
        <form method="POST" action="{{ url('/reset-password') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>
            @if ($errors->has('email'))
                <span class="form-error">{{ $errors->first('email') }}</span>
            @endif

            <label for="password">New Password</label>
            <input id="password" type="password" name="password" value="{{ old('password') }}" required>
            @if ($errors->has('password'))
                <span class="form-error">{{ $errors->first('password') }}</span>
            @endif

            <label for="password_confirmation">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                value="{{ old('password_confirmation') }}" required>
            @if ($errors->has('password_confirmation'))
                <span class="form-error">{{ $errors->first('password_confirmation') }}</span>
            @endif
            <button type="submit">Update Password</button>

            @if (session('success'))
                <p class="form-success">{{ session('success') }}</p>
            @endif
        </form>
    </div>
@endsection
