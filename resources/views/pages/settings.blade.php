@extends('layouts.app')
@section('title', 'Project Settings')
@section('content')
    @if (!$project->archived && $isCoordinator)
        <script src="{{ asset('js/utils.js') }}" defer></script>
        <script src="{{ asset('js/projectSettingsCoordinator.js') }}" defer></script>
    @endif
    <script src="{{ asset('js/projectSettings.js') }}" defer></script>
    <section id="settings">
        <h2 class="page-title">Settings</h2>
        <div class="project-container">
            <h2 class="container-title">{{ $project->name }}</h2>
            <div id="project-description-container">
                <strong>Description:</strong>
                @if (!$project->archived && $isCoordinator)
                    <a href="#" id="edit-description-icon" class="projectMembersIcon icon-button tooltip right"
                        data-text="Edit Description">
                        <i class="fa fa-pencil"></i>
                    </a>
                @endif
                <div id="description-display" style="display: block;">
                    <p>{{ $project->description }}</p>
                </div>

                @if (!$project->archived && $isCoordinator)
                    <form id="description-form" style="display: none;"
                        action="{{ route('project.changeDescription', ['project' => $project->id]) }}" method="POST">
                        @csrf
                        <textarea id="description-input" name="description" rows="4" style="width: 100%;" required>{{ $project->description }}</textarea>
                        <button type="submit" id="save-description">Save</button>
                        <button type="button" id="cancel-edit-description" class="btn-cancel">Cancel</button>
                    </form>
                @endif
            </div>
            <p class="project-coordinator">
                <strong>Coordinator:</strong>
                <span id="current-coordinator">{{ $project->coordinator->name }}</span>
                @if (!$project->archived && $isCoordinator)
                    <a href="#" id="editCoordinatorIcon" class="projectMembersIcon icon-button tooltip right"
                        data-text="Change Coordinator">
                        <i class="fa fa-pencil"></i>
                    </a>
                @endif
            </p>

            @if (!$project->archived && $isCoordinator)
                <div id="change-coordinator-section" style="display:none">
                    <div id="project-container" data-project-id="{{ $project->id }}">
                        <label for="new-coordinator">Select new coordinator:</label>
                        <div style="display: flex; align-items: center;">
                            <select id="new-coordinator" name="new_coordinator" required>
                                <option value="" disabled>Select a coordinator</option> <!-- Placeholder option -->
                                @foreach ($project->members as $member)
                                    <option value="{{ $member->id }}"
                                        {{ (int) $member->id === (int) $project->project_coordinator_id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>

                            <form id="change-coordinator-form" method="POST" class="confirmation">
                                @csrf
                                @method('PUT')
                                <button type="submit" id="coordinatorChangeButton"
                                    class="projectMembersIcon icon-button confirm-action">
                                    <i class="fa fa-exchange"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div id="privacy-settings">
                <p>
                    <strong>Privacy:</strong>
                    {{ $project->ispublic ? 'Public' : 'Private' }}
                    @if (!$project->archived && $isCoordinator)
                        <a href="#" id="edit-privacy-icon" class="projectMembersIcon icon-button tooltip right"
                            data-text="Edit Privacy">
                            <i class="fa fa-pencil"></i>
                        </a>
                    @endif
                </p>

                @if (!$project->archived && $isCoordinator)
                    <form action="{{ route('project.updatePrivacy', ['project' => $project->id]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <label for="visibility">Change project's privacy:</label>
                        <select id="visibility" name="visibility">
                            <option value="public" {{ $project->ispublic ? 'selected' : '' }}>Public</option>
                            <option value="private" {{ !$project->ispublic ? 'selected' : '' }}>Private</option>
                        </select>


                        <button type="submit" id="save-privacy">Save</button>
                        <button type="button" id="cancel-edit-privacy" class="btn-cancel">Cancel</button>
                    </form>
                @endif

            </div>

            <div id="permissions-settings">
                <p>
                    <strong>Project Members Permissions:</strong>
                    @if (!$project->archived && $isCoordinator)
                        <a href="#" class="edit-permission-icon projectMembersIcon icon-button tooltip right"
                            data-text="Edit Permissions" id="edit-permission-icon">
                            <i class="fa fa-pencil"></i>
                        </a>
                    @endif
                </p>
                <ul>
                    @foreach ([
            'add_deadline_permission' => 'Add Deadlines',
            'create_task_permission' => 'Create Tasks',
            'edit_task_permission' => 'Edit Tasks',
            'assign_tasks_permission' => 'Assign Users to Tasks',
            'create_tasktable_permission' => 'Create Task Tables',
            'add_member_permission' => 'Add Members',
            'view_deleted_tasks_permission' => 'View Deleted Tasks',
        ] as $permission => $label)
                        @if ($project->$permission)
                            <li>
                                <span class="permission-label">
                                    {{ $label }}
                                </span>
                            </li>
                        @endif
                    @endforeach
                </ul>

                @if (!$project->archived && $isCoordinator)
                    <!-- Permission Edit Form (Initially Hidden) -->
                    <form id="permissions-form"
                        action="{{ route('project.updatePermissions', ['project' => $project->id]) }}" method="POST"
                        style="display: none;">
                        @csrf
                        @method('PUT')
                        <p><strong>Edit Permissions:</strong></p>
                        @foreach ([
            'add_deadline_permission' => 'Add Deadlines',
            'create_task_permission' => 'Create Tasks',
            'edit_task_permission' => 'Edit Tasks',
            'assign_tasks_permission' => 'Assign Users to Tasks',
            'create_tasktable_permission' => 'Create Task Tables',
            'add_member_permission' => 'Add Members',
            'view_deleted_tasks_permission' => 'View Deleted Tasks',
        ] as $permission => $label)
                            <div>
                                <label for="{{ $permission }}">
                                    <input type="checkbox" id="{{ $permission }}"
                                        name="permissions[{{ $permission }}]"
                                        {{ $project->$permission ? 'checked' : '' }}>
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                        <button type="submit">Save</button>
                        <button type="button" id="cancel-permissions-edit" class="btn-cancel">Cancel</button>
                    </form>
                @endif
            </div>
            @if ($isCoordinator)
                <form action="{{ route('projects.toggleArchive', ['project' => $project->id]) }}" method="POST"
                    class="delete">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="delete-account-button confirm-action">
                        @if ($project->archived)
                            <i class="fa fa-folder-open"></i>
                        @else
                            <i class="fa fa-folder"></i>
                        @endif
                        <span>{{ $project->archived ? 'Unarchive Project' : 'Archive Project' }}</span>
                    </button>
                </form>
            @else
                <div class="project-status-indicator">
                    @if ($project->archived)
                        <p><i class="fa fa-folder"></i> This project is currently archived.</p>
                    @else
                        <p><i class="fa fa-folder-open"></i> This project is currently active.</p>
                    @endif
                </div>

            @endif


            @if ($isMember)
                <form id="leaveProject-form"
                    action="{{ route('project.removeAccount', ['project' => $project->id, 'user' => $account->id]) }}"
                    method="POST" class="delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-account-button confirm-action">Leave Project </button>
                </form>
            @endif
        </div>
        @if (!$project->archived && $isMember)
            <div class="project-container">
                <h2 class="container-title">Customize Side Components</h2>
                @php
                    $defaultComponent = 'none';

                    $topRightComponent = $userSettings['forum_component'] === 'RightUp' ? 'forum_component' : ($userSettings['analytics_component'] === 'RightUp' ? 'analytics_component' : ($userSettings['members_component'] === 'RightUp' ? 'members_component' : ($userSettings['productivity_component'] === 'RightUp' ? 'productivity_component' : $defaultComponent)));

                    $bottomRightComponent = $userSettings['forum_component'] === 'RightDown' ? 'forum_component' : ($userSettings['analytics_component'] === 'RightDown' ? 'analytics_component' : ($userSettings['members_component'] === 'RightDown' ? 'members_component' : ($userSettings['productivity_component'] === 'RightDown' ? 'productivity_component' : $defaultComponent)));

                    $topLeftComponent = $userSettings['forum_component'] === 'LeftUp' ? 'forum_component' : ($userSettings['analytics_component'] === 'LeftUp' ? 'analytics_component' : ($userSettings['members_component'] === 'LeftUp' ? 'members_component' : ($userSettings['productivity_component'] === 'LeftUp' ? 'productivity_component' : $defaultComponent)));

                    $bottomLeftComponent = $userSettings['forum_component'] === 'LeftDown' ? 'forum_component' : ($userSettings['analytics_component'] === 'LeftDown' ? 'analytics_component' : ($userSettings['members_component'] === 'LeftDown' ? 'members_component' : ($userSettings['productivity_component'] === 'LeftDown' ? 'productivity_component' : $defaultComponent)));
                @endphp

                <form action="{{ route('project.updateComponent', ['project' => $project->id]) }}" method="POST">
                    @csrf
                    <div class="customise-form">
                        <div class="component-config">
                            <label for="left-up-component">Top Left Component</label>
                            <select id="left-up-component" name="topLeftComponent" class="component-select">
                                <option value="none" {{ $topLeftComponent === 'none' ? 'selected' : '' }}>None</option>
                                <option value="forum_component"
                                    {{ $topLeftComponent === 'forum_component' ? 'selected' : '' }}>Forum Messages</option>
                                <option value="analytics_component"
                                    {{ $topLeftComponent === 'analytics_component' ? 'selected' : '' }}>History</option>
                                <option value="members_component"
                                    {{ $topLeftComponent === 'members_component' ? 'selected' : '' }}>Project Members
                                </option>
                                <option value="productivity_component"
                                    {{ $topLeftComponent === 'productivity_component' ? 'selected' : '' }}>Productivity
                                </option>
                            </select>
                        </div>

                        <div class="component-config">
                            <label for="right-up-component">Top Right Component</label>
                            <select id="right-up-component" name="topRightComponent" class="component-select">
                                <option value="none" {{ $topRightComponent === 'none' ? 'selected' : '' }}>None</option>
                                <option value="forum_component"
                                    {{ $topRightComponent === 'forum_component' ? 'selected' : '' }}>Forum Messages
                                </option>
                                <option value="analytics_component"
                                    {{ $topRightComponent === 'analytics_component' ? 'selected' : '' }}>History</option>
                                <option value="members_component"
                                    {{ $topRightComponent === 'members_component' ? 'selected' : '' }}>Project Members
                                </option>
                                <option value="productivity_component"
                                    {{ $topRightComponent === 'productivity_component' ? 'selected' : '' }}>Productivity
                                </option>
                            </select>
                        </div>

                        <div class="component-config">
                            <label for="left-down-component">Bottom Left Component</label>
                            <select id="left-down-component" name="bottomLeftComponent" class="component-select">
                                <option value="none" {{ $bottomLeftComponent === 'none' ? 'selected' : '' }}>None
                                </option>
                                <option value="forum_component"
                                    {{ $bottomLeftComponent === 'forum_component' ? 'selected' : '' }}>Forum Messages
                                </option>
                                <option value="analytics_component"
                                    {{ $bottomLeftComponent === 'analytics_component' ? 'selected' : '' }}>History</option>
                                <option value="members_component"
                                    {{ $bottomLeftComponent === 'members_component' ? 'selected' : '' }}>Project Members
                                </option>
                                <option value="productivity_component"
                                    {{ $bottomLeftComponent === 'productivity_component' ? 'selected' : '' }}>Productivity
                                </option>
                            </select>
                        </div>

                        <div class="component-config">
                            <label for="right-down-component">Bottom Right Component</label>
                            <select id="right-down-component" name="bottomRightComponent" class="component-select">
                                <option value="none" {{ $bottomRightComponent === 'none' ? 'selected' : '' }}>None
                                </option>
                                <option value="forum_component"
                                    {{ $bottomRightComponent === 'forum_component' ? 'selected' : '' }}>Forum Messages
                                </option>
                                <option value="analytics_component"
                                    {{ $bottomRightComponent === 'analytics_component' ? 'selected' : '' }}>History
                                </option>
                                <option value="members_component"
                                    {{ $bottomRightComponent === 'members_component' ? 'selected' : '' }}>Project Members
                                </option>
                                <option value="productivity_component"
                                    {{ $bottomRightComponent === 'productivity_component' ? 'selected' : '' }}>Productivity
                                </option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn">Save</button>
                </form>
            </div>
        @endif
    </section>
    @include('partials.sidebar', ['project' => $project, 'page' => 'settings'])
@endsection
