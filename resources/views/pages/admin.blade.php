@extends('layouts.app')

@section('title', 'Users and Projects Management')

@section('content')
    <!-- Load Scripts Conditionally -->
    @if (request()->routeIs('admin.accounts'))
        <script src="{{ asset('js/userSearch.js') }}" defer></script>
    @elseif(request()->routeIs('admin.projects'))
        <script src="{{ asset('js/projectSearch.js') }}" defer></script>
    @endif
    <script src="{{ asset('js/search.js') }}" defer></script>

    <div class="header-bar">
        <a href="{{ route('projects') }}" class="back-arrow"><i class="fas fa-arrow-left"></i>
            <h3>Projects</h3>
        </a>
    </div>

    <h2 class="page-title">Administer</h2>
    <!-- Tabs for Users and Projects -->
    <div id="admin-tabs-container">
        <div class="admin-tabs">
            <a href="{{ route('admin.accounts') }}"
                class="admin-tab-button {{ request()->routeIs('admin.accounts') ? 'active' : '' }}" id="accounts-tab">
                <i class="fas fa-users-gear"></i> Users
            </a>
            <a href="{{ route('admin.projects') }}"
                class="admin-tab-button {{ request()->routeIs('admin.projects') ? 'active' : '' }}" id="projects-tab">
                <i class="fas fa-gears"></i> Projects
            </a>
        </div>
    </div>

    <!-- Conditionally Include Partials -->
    @if (request()->routeIs('admin.accounts'))
        @include('partials.adminViewAccounts', compact('users', 'search', 'filter'))
    @elseif(request()->routeIs('admin.projects'))
        @include('partials.adminViewProjects', compact('projects', 'search', 'filter'))
    @endif

@endsection
