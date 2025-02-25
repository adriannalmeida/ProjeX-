<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="public-token" content="{{ env('PUBLIC_ACCESS_TOKEN') }}">

    <!-- Open Graph Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="ProjeX - Take Control of Your Projects">
    <meta property="og:description"
        content="The best platform to coordinate projects, organize tasks, and maintain clear communication.">
    <meta property="og:image" content="http://localhost:8000/assets/projex_logo.png">
    <meta property="og:url" content="http://localhost:8000/mainPage">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ProjeX">
    <meta property="og:locale" content="{{ app()->getLocale() }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/faviconWhite.ico') }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Styles -->
    <link href="{{ asset('css/milligram.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/project.css') }}" rel="stylesheet">
    <link href="{{ asset('css/invite.css') }}" rel="stylesheet">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

    @if (Request::is('account') ||
            Request::is('account/edit') ||
            Request::is('account/manage/*') ||
            Request::is('project/*/projectMembers/*'))
        <link href="{{ asset('css/account.css') }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @if (Request::is('admin*'))
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.0"></script>

    <script>
        window.userId = @json(Auth::check() ? Auth::user()->id : null);
        window.Laravel = {
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
    </script>
    <script src={{ asset('js/app.js') }} defer></script>
    <script src="{{ asset('js/utils.js') }}" defer></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js" defer></script>

    @if (Request::is('project/*'))
        <script src="{{ asset('js/sideBar.js') }}" defer></script>
    @endif
</head>

<body>
    <header>
        <a href="#content" class="jump-link">Go to contents</a>
        @if (Request::is('project/*/task/*'))
            <a href="#taskPanel" class="jump-link">Go to task details</a>
        @endif
        @if (Auth::check())
            <a href="{{ route('account.taskHub') }}" class="jump-link">Go to Task Hub</a>
            @if (Auth::user()->admin)
                <a href="{{ route('admin.accounts') }}" class="jump-link">Go to Admin page</a>
            @endif
        @endif
        <div class="header">
            @if (Auth::check())
                <div class="logo tooltip right" data-text="Projects page">
                    <a href="{{ url('/projects') }}">
                        <img src="{{ asset('assets/projex_logo.png') }}" alt="ProjeX logo">
                    </a>
                </div>

                @if (Request::is('project/*'))
                    <h1 id="projectName">{{ $project->name }}</h1>
                @endif
                <div class="header-icons">
                    <div class="dropdown">
                        <a href="{{ url('/account') }}#invitations" class="dropdown-toggle"
                            id="dropdown-toggle-invite">
                            <i class="fas fa-envelope"></i>
                            @if ($invitationCount > 0)
                                <span id="invitation-badge" class="badge">{{ $invitationCount }}</span>
                            @endif
                        </a>
                        <div class="dropdown-content invitations-dropdown">
                            @if ($headerInvitations->isEmpty())
                                <p>No new Invitations.</p>
                            @else
                                @foreach ($headerInvitations as $invitation)
                                    <div class="container-list-item invitation-item">
                                        <p>{{ $invitation->getProject->name }}</p>
                                        <div class="accept-actions">
                                            <form action="{{ route('invitation.accept', $invitation->id) }}"
                                                method="POST" class="accept-form">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="accept-btn" title="Accept"><i
                                                        class="fas fa-check"></i></button>
                                            </form>
                                            <form action="{{ route('invitation.decline', $invitation->id) }}"
                                                method="POST" class="decline-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="decline-btn" title="Decline"><i
                                                        class="fas fa-times"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                <a href="{{ route('account.show') }}#invitations" class="see-all-invitations">See all
                                    invitations...</a>
                            @endif
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="{{ url('/account') }}#notifications" class="dropdown-toggle"
                            id="dropdown-toggle-notification">
                            <i class="fas fa-bell"></i>
                            @if ($notificationCount > 0)
                                <span class="badge" id="notification-badge">{{ $notificationCount }}</span>
                            @endif
                        </a>
                        <div class="dropdown-content notifications-dropdown">
                            @if ($headerNotifications->isEmpty())
                                <p>No new Notifications.</p>
                            @else
                                @foreach ($headerNotifications as $notification)
                                    <div class="container-list-item notification-item">
                                        <p>{{ $notification->description() }}</p>
                                        <div class="accept-actions">
                                            <form action="{{ route('notification.check', $notification->id) }}"
                                                method="POST" class="decline-form">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="accept-btn" title="Accept"><i
                                                        class="fas fa-check"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                <a href="{{ route('account.show') }}#notifications" class="see-all-invitations">See
                                    all notifications...</a>
                            @endif
                        </div>
                    </div>
                    <div class="profile-menu">
                        <nav class="profile-dropdown">
                            <a href="{{ url('/account') }}">
                                <img src="{{ Auth::user()->getAccountImage() }}" class="round-photo"
                                    alt="Profile Picture">
                            </a>
                            <ul class="dropdown-content">
                                <li class="username">{{ Auth::user()->username }}</li>
                                <li><a href="{{ route('account.taskHub') }}"><i class="fas fa-list-check"></i> Task
                                        Hub</a></li>
                                @if (Auth::user()->admin)
                                    <li><a href="{{ route('admin.accounts') }}"><i
                                                class="fas fa-screwdriver-wrench"></i> Admin</a></li>
                                @endif
                                <li><a href="{{ url('/logout') }}"><i class="fas fa-arrow-right-from-bracket"></i>
                                        Logout</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            @else
                <div class="logo tooltip right" data-text="Projects page">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('assets/projex_logo.png') }}" alt="ProjeX logo">
                    </a>
                </div>
                <div class="header-icons">
                    <a href="/login">Login</a>
                    <a href="/register">Sign up</a>
                </div>
            @endif

        </div>
    </header>
    @if (session('message'))
        <div id="notification">
            <div class="{{ session('message.type') }}">
                @if (session('message.type') === 'success')
                    <i class="material-symbols-outlined green"> check_circle </i>
                @elseif(session('message.type') === 'error')
                    <i class="material-symbols-outlined red"> error </i>
                @endif
                <p>{{ session('message.text') }}</p>
                <div class="notification-progress"></div>
            </div>
        </div>
    @endif
    <main id="content"
        class="{{ Request::is('project/*') ? 'project-page align-start' : (!(Request::is('mainPage') or Request::is('login') or Request::is('register') or Request::is('forgot-password') or Request::is('reset-password/*')) ? 'align-start' : '') }}">
        @yield('content')
    </main>
    <footer>
        @unless (Request::is('project/*'))
            <div class="footer">
                <a href="{{ url('/projects') }}" class="logo hide-mobile tooltip right" data-text="Projects page">
                    <img src="{{ asset('assets/projex_logo_light.png') }}" alt="ProjeX logo">
                </a>
                <div class="links">
                    <a href="{{ route('main.page') }}">Home Page</a>
                    <br>
                    <a href="{{ route('aboutUs.page') }}"> About ProjeX </a>
                </div>

                <div class="contacts">
                    <h4>Contacts</h4>
                    <p>projex@projex.com</p>
                </div>
            </div>
        @endunless
    </footer>
</body>

</html>
