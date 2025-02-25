<div id="side-bar">
    <div class="sidebar-button">
        <a id="open-side-bar-mobile">
            <i class="fa fa-bars fa-lg"></i>
            <span class="nav-text"></span>
        </a>
    </div>
    <ul>
        <li>
            <a id="open-side-bar">
                <i class="fa fa-bars fa-lg"></i>
                <span class="nav-text">Sidebar</span>
            </a>
        </li>
        <li>
            <a href="{{ $page === 'project' ? '' : route('project.show', ['project' => $project->id]) }}"
                class="{{ $page === 'project' ? 'selected' : '' }}">
                <i class="fa fa-house fa-lg"></i>
                <span class="nav-text">Project</span>
            </a>
        </li>
        <li>
            <a href="{{ $page === 'projectTable' ? '' : route('project.table.show', ['project' => $project->id]) }}"
                class="{{ $page === 'projectTable' ? 'selected' : '' }}">
                <i class="fa fa-table-list"></i>
                <span class="nav-text">Table View</span>
            </a>
        </li>
        <li>
            <a href="{{ $page === 'members' ? '' : route('projectMembers.show', ['project' => $project->id]) }}"
                class="{{ $page === 'members' ? 'selected' : '' }}">
                <i class="fa fa-users fa-lg"></i>
                <span class="nav-text">Members</span>
            </a>
        </li>
        <li>
            <a href="{{ $page === 'history' ? '' : route('project.timeline', ['project' => $project->id]) }}"
                class="{{ $page === 'history' ? 'selected' : '' }}">
                <i class="fa fa-history fa-lg"></i>
                <span class="nav-text">History</span>
            </a>
        </li>
        <li>
            <a href="{{ $page === 'forum' ? '' : route('project.forum', ['project' => $project->id]) }}"
                class="{{ $page === 'forum' ? 'selected' : '' }}">
                <i class="fa fa-comments fa-lg"></i>
                <span class="nav-text">Forum</span>
            </a>
        </li>
        <li>
            <a href="{{ $page === 'settings' ? '' : route('project.settings', ['project' => $project->id]) }}"
                class="{{ $page === 'settings' ? 'selected' : '' }}">
                <i class="fa fa-cog fa-lg"></i>
                <span class="nav-text">Settings</span>
            </a>
        </li>
        @if ($page === 'account')
            <li>
                <a href="{{ url()->current() }}" class="selected">
                    <i class="fa fa-user fa-lg"></i>
                    <span class="nav-text">Account</span>
                </a>
            </li>
        @endif
    </ul>
</div>
