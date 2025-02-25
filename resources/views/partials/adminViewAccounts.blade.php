<div id="accounts-tab-content" class="tab-content.active relative">

    <div class="search-header-bar header-bar search-button-container">
        @include('partials.searchBar', [
            'formAction' => route('admin.accounts'),
            'placeholder' => 'Search accounts...',
            'search' => request()->input('search') ?? '', // Get the current search query
            'filters' => ['All Accounts', 'Blocked', 'Not Blocked'], // The available filter options
            'currentFilter' => $filter ?? '',
            'resultsUrl' => route('users.search.ajax'),
            'results' => $users->map(
                fn($user) => [
                    'url' => route('account.show', ['user' => $user->id]), // URL for each result
                    'name' => $user->name, // Display the name of the user
                    'description' => $user->email,
                ]),
        ])
        <form method="GET" action="{{ route('register') }}" class="search-btn">
            <button type="submit"><i class="fas fa-user-plus"></i><span> Add User</span></button>
        </form>
    </div>

    <div class="project-container">
        <table id="users_table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th class="hide-tablet">Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if (!$users->isEmpty())
                    @foreach ($users as $key => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $key }}</td>
                            <td>{{ $user->name }}</td>
                            <td class="hide-tablet">{{ $user->email }}</td>
                            <td>
                                @if (!$user->admin)
                                    <form action="{{ url('/account/manage', $user->id) }}" method="GET"
                                        style="display:inline;">
                                        <button type="submit" class="iconB"><i
                                                class="fas fa-edit"></i><span>Edit</span></button>
                                    </form>
                                    <form action="{{ url('/admin/block', $user->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        @if ($user->blocked)
                                            <button type="submit" class="iconB"><i
                                                    class="fas fa-lock-open"></i><span>Unblock</span></button>
                                        @else
                                            <button type="submit" class="iconB"><i
                                                    class="fas fa-lock"></i><span>Block</span></button>
                                        @endif
                                    </form>
                                    <form action="{{ route('account.delete', $user->id) }}" class="delete-user"
                                        method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="iconB confirm-action">
                                            <i class="fas fa-trash-alt"></i><span>Delete</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center;">No users found</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div id="pag_all" class="pagination">
            {{ $users->appends(request()->query() + ['filter' => 'all'], 'all_users')->links() }}
        </div>
    </div>
</div>
