@extends('layouts.app')

@section('content')
    <script src={{ asset('js/register.js') }} defer></script>

    <div class="form-box">
        @if ($isAdmin)
            <h2 class="form-title">Register a new ProjeX Account</h2>
        @else
            <h2 class="form-title">Register your ProjeX Account</h2>
        @endif
        <input type="hidden" id="user_country" value="">
        <form method="POST" action="{{ route('register.submit') }}">
            @csrf
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus>
            @if ($errors->has('username'))
                <span class="form-error">
                    {{ $errors->first('username') }}
                </span>
            @endif

            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required>
            @if ($errors->has('name'))
                <span class="form-error">
                    {{ $errors->first('name') }}
                </span>
            @endif

            <label for="email">E-Mail Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            @if ($errors->has('email'))
                <span class="form-error">
                    {{ $errors->first('email') }}
                </span>
            @endif

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
            @if ($errors->has('password'))
                <span class="form-error">
                    {{ $errors->first('password') }}
                </span>
            @endif

            <label for="password-confirm">Confirm Password</label>
            <input id="password-confirm" type="password" name="password_confirmation" required>

            <label for="country">Country (Optional)</label>
            <select id="country" name="country">
                <option value="">Select a country</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" {{ old('country') == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
            @if ($errors->has('country'))
                <span class="form-error">
                    {{ $errors->first('country') }}
                </span>
            @endif

            <label for="city">City</label>
            <select id="city" name="city" disabled onchange="updateCityId()">
                <option value="">Select a city</option>
            </select>
            <input type="hidden" id="cityId" name="cityId" value="{{ old('cityId') }}">
            @if ($errors->has('city'))
                <span class="form-error">
                    {{ $errors->first('city') }}
                </span>
            @endif

            <label for="workfield">Workfield</label>
            <input id="workfield" type="text" name="workfield" value="{{ old('workfield') }}">
            @if ($errors->has('workfield'))
                <span class="form-error">
                    {{ $errors->first('workfield') }}
                </span>
            @endif

            <button type="submit">
                {{ Auth::check() && Auth::user()->admin ? 'Add Account' : 'Register' }}
            </button>
            @if (!Auth::check())
                <a class="btn" href="{{ route('login') }}">Login</a>
            @endif
            @if (Auth::check() && Auth::user()->admin)
                <a class="btn" href="{{ route('admin.accounts') }}">Go back</a>
            @endif
        </form>
    </div>
@endsection
