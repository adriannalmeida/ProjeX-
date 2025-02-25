@extends('layouts.app')
@section('title', 'Project Members')
@section('content')
    <script src="{{ asset('js/invite.js') }}" defer></script>
    @if (!$project->archived && $isCoordinator)
        <script src="{{ asset('js/utils.js') }}" defer></script>
        <script src="{{ asset('js/projectMembersCoordinator.js') }}" defer></script>
    @endif

    <section id="project-members">
        @if (!$project->archived && ($isCoordinator || $project->add_member_permission))
            <h2 class="page-title">Invite User</h2>
            <div class="invite-user">
                <div id="project-container" data-project-id="{{ $project->id }}">
                    <form method="POST" action="{{ route('invitation.invite', ['project' => $project->id]) }}"
                        id="invite-user-form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <label for="username-email">Invite User</label>
                        <input type="text" id="username-email" name="username-email"
                            placeholder="Enter Username or E-mail" required>
                        <div id="suggestions" class="suggestions-box"></div>
                        <button type="submit">Send Invitation</button>
                    </form>
                </div>
            </div>
        @endif

        <h2 class="page-title">Project Members</h2>
        <div class="project-container">
            <table id="project-members-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody class="project-members-rows">
                    @foreach ($projectMembers as $member)
                        <tr style="cursor: pointer;" class="focusable">
                            <td class="project-members-name"
                                onclick="window.location='{{ route('memberAccount.show', ['project' => $project->id, 'member' => $member->id]) }}'">
                                {{ $member->name }}</td>
                            <td>
                                {{ $member->email }}
                                @if ($isCoordinator && $member->id != Auth::user()->id)
                                    <form class="remove-member-form confirmation tooltip left" data-text="Remove User"
                                        method="POST"
                                        action="{{ route('project.removeAccount', ['project' => $project->id, 'user' => $member->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="projectMembersIcon icon-button confirm-action">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div id="pag_invitations" class="pagination">
                {{ $projectMembers->links() }}
            </div>
        </div>
        @if (!$project->archived)
            <h2 class="page-title">Invited Users</h2>
            <div class="project-container">

                <table id="invited-users-table">

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody class="invited-accounts-rows">

                        @foreach ($invitedAccounts as $invited)
                            <tr>
                                <td>{{ $invited->name }}</td>
                                <td>{{ $invited->email }}</td>
                            </tr>
                        @endforeach
                        @if ($invitedAccounts->isEmpty())
                            <tr class="no-invites">
                                <td>
                                    <p class="no-invites">No users have been invited yet.</p>
                                </td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div id="pag_invitedUsers" class="pagination">
                    {{ $invitedAccounts->links() }}
                </div>
            </div>
        @endif

    </section>
    @include('partials.sidebar', ['project' => $project, 'page' => 'members'])

@endsection
