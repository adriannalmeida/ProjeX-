@extends('layouts.app')

@section('content')
    <script src={{ asset('js/register.js') }} defer></script>
    @if (Request::is('account/edit'))
        <script src={{ asset('js/accountEdit.js') }} defer></script>
    @endif

    @if ($isAdminViewingFromAdmin)
        <div class="header-bar">
            <a href="{{ route('admin.accounts') }}" class="back-arrow"><i class="fas fa-arrow-left"></i>
                <h3>Admin Page</h3>
            </a>
        </div>
    @else
        <div class="header-bar">
            <a href="{{ route('account.show') }}" class="back-arrow"><i class="fas fa-arrow-left"></i>
                <h3>Profile Page</h3>
            </a>
        </div>
    @endif

    <div class="form-box">
        @if ($isAdminViewingFromAdmin)
            <h2 class="form-title">Edit the ProjeX Account</h2>
        @else
            <h2 class="form-title">Edit your ProjeX Account</h2>
        @endif
        <!-- Profile Details Form -->
        <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if (Auth::user()->admin)
                <input type="hidden" name="user_id" value="{{ $account->id }}">
            @endif
            <input type="hidden" id="user_country" value="{{ $account_country }}">
            <div class="profile-photo-container">
                <div class="profile-photo">
                    <img src="{{ $account->getAccountImage() }}" alt="Profile Picture">
                </div>
                @if (!$isAdminViewingFromAdmin)
                    <label class="edit-profile-upload-button focusable tooltip right"
                        data-text="Edit Profile Picture" for="file-input">
                        <i class="fas fa-pencil-alt"></i>
                    </label>
                    <input id="file-input" type="file" name="account_image" accept="image/*" style="display: none;">
                    @if ($errors->has('account_image'))
                        <span class="form-error">{{ $errors->first('account_image') }}</span>
                    @endif
                    <p id="file-name" style="margin-top: 10px; font-size: 1rem; color: #333;">No file selected</p>
                @endif
            </div>
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $account->name) }}" required>
            @if ($errors->has('name'))
                <span class="form-error">{{ $errors->first('name') }}</span>
            @endif

            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username', $account->username) }}"
                required>
            @if ($errors->has('username'))
                <span class="form-error">{{ $errors->first('username') }}</span>
            @endif

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $account->email) }}" required>
            @if ($errors->has('email'))
                <span class="form-error">{{ $errors->first('email') }}</span>
            @endif

            <label for="country">Country</label>
            <select id="country" name="country">
                <option value="">Select a country</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                        {{ (string) old('country', $account_country ?? null) === (string) $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach

            </select>
            <label for="city" class="form-label">City</label>
            <select id="city" name="city" disabled onchange="updateCityId()">
                <option value="">Select a city</option>
            </select>
            <input type="hidden" id="cityId" name="cityId" value="{{ $account_city }}">

            @if ($errors->has('city'))
                <span class="form-error">{{ $errors->first('city') }}</span>
            @endif

            <label for="workfield">Work Field</label>
            <input id="workfield" type="text" name="workfield" value="{{ old('workfield', $account->workfield) }}">
            @if ($errors->has('workfield'))
                <span class="form-error">{{ $errors->first('workfield') }}</span>
            @endif

            <button type="submit">Save Changes</button>
            @if (!$isAdminViewingFromAdmin)
                <a class="btn" href="{{ route('forgot.password.show', ['email' => $account->email]) }}">Change your
                    password?</a>
            @endif
        </form>

        @if (!$account->admin)
            <form method="POST" action="{{ route('account.delete', $account->id) }}" class="delete">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-account-button confirm-action">Delete Account </button>
            </form>
        @endif
    </div>

@endsection
