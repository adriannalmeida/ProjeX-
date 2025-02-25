@extends('layouts.app')

@section('content')
    <script src={{ asset('js/account.js') }} defer></script>

    <section id="account-info">
        <div class="header-bar">
            @if (Request::is('project/*/projectMembers/*'))
                <a href="{{ route('projectMembers.show', $project->id) }}" class="back-arrow"><i class="fas fa-arrow-left"></i>
                    <h3>Project</h3>
                </a>
            @else
                <a href="{{ route('projects') }}" class="back-arrow"><i class="fas fa-arrow-left"></i>
                    <h3>Projects</h3>
                </a>
            @endif
        </div>
        @if ($account->blocked)
            <div class="blocked-user-message">
                <i class="fas fa-ban"></i>
                <p><strong>User Blocked</strong></p>
            </div>
        @endif
        <div class="profile-container">
            <!-- Left Section: Profile Photo and Edit Button -->
            <div class="left-section">
                <div class="profile-photo">
                    <img src="{{ $account->getAccountImage() }}" alt="Profile Picture">
                </div>
                @if ($account == Auth::user())
                    <a href="{{ route('account.edit') }}" class="edit-profile-button">Edit Profile</a>
                @endif
            </div>

            <!-- Right Section: User Details -->
            <div class="right-section">
                <div class="profile-header-bar">
                    <h2>{{ $account->name }}</h2>
                    <p>{{ '@' . $account->username }}</p>
                    <a href="{{ url('/logout') }}" class="logout-btn icon-button"><i
                            class="fas fa-arrow-right-from-bracket"></i> Logout</a>
                </div>
                <ul class="contact-info">
                    <li><strong>Email:</strong> {{ $account->email }}</li>
                    <li><strong>Location:</strong> {{ $city ? $city->name : 'No city' }},
                        {{ $country ? $country->name : 'No country' }}</li>
                    <li><strong>Work Field:</strong> {{ $account->workfield }}</li>
                </ul>
            </div>
        </div>

        @if (Request::is('project/*'))
            @include('partials.sidebar', ['project' => $project, 'page' => 'account'])
        @endif

        @if ($account == Auth::user())



            <!-- Invitations Section -->
            <div id="invitations" class="project-container invitations-section">
                <h2 class="container-title">Pending Invitations</h2>
                @if ($invitations->isEmpty())
                    <p class="no-invitations">You have no pending invitations.</p>
                @else
                    <ul class="container-list invitations-list">
                        @foreach ($invitations as $invitation)
                            <li class="container-list-item invitation-item">
                                <p class="invitation-project">{{ $invitation->getProject->name }}</p>
                                <div class="accept-actions">
                                    <form action="{{ route('invitation.accept', $invitation->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="accept-btn tooltip left" data-text="Accept"><i
                                                class="fas fa-check"></i></button>
                                    </form>
                                    <form action="{{ route('invitation.decline', $invitation->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="decline-btn tooltip left" data-text="Decline"><i
                                                class="fas fa-times"></i></button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div id="pag_invitations" class="pagination">
                        {{ $invitations->links() }}
                    </div>
                @endif
            </div>

            <div id="notifications" class="project-container notifications-section">
                <h2 class="container-title">Pending Notifications</h2>
                @if ($notifications->isEmpty())
                    <p class="no-notifications">You have no pending notifications.</p>
                @else
                    <ul class="container-list notifications-list">
                        @foreach ($notifications as $notification)
                            <li class="container-list-item notification-item">
                                <p class="notification-description">{{ $notification->description() }}</p>
                                <p class="notification-date">{{ $notification->create_date->format('d M Y, H:i') }}</p>
                                <div class="accept-actions">
                                    <form action="{{ route('notification.check', $notification->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="accept-btn tooltip left" data-text="Accept"><i
                                                class="fas fa-check"></i></button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div id="pag_notifications" class="pagination">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        @endif
    </section>
@endsection
