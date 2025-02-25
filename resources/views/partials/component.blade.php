<div class="component">
    @switch($component)
        @case('forum_component')
            <!-- Forum Section -->
            <script src="{{ asset('js/forumComponent.js') }}" defer></script>
            <a href="{{ route('project.forum', ['project' => $project->id]) }}" class="view-all-link">Forum</a>
            <div class="forum-component" id ="forumMessagesContainer">
                @if ($forumMessages->isEmpty())
                    <div class="no-messages">
                        <p>No messages yet.</p>
                    </div>
                @else
                    @foreach ($forumMessages->reverse() as $message)
                        <div class="forum-message">
                            <p><strong>{{ $message->getAccount->name }}:</strong> {{ $message->content }}</p>
                        </div>
                    @endforeach
                    @if ($forumMessages->count() == 30)
                        <a href="{{ route('project.forum', ['project' => $project->id]) }}" class="view-all-link">See all</a>
                    @endif
                @endif
            </div>
            @if ($isMember)
                <div class="forum-form-component">
                    <form id="forumMessageForm" method="POST" action="{{ route('forum.store', ['project' => $project->id]) }}"
                        data-account-id="{{ Auth::user()->id }}">
                        @csrf
                        <textarea name="message" id="messageContentComponent" rows="4" placeholder="Message..." required></textarea>
                        <button type="submit" class="send-button tooltip left" data-text="Send Message">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            @endif
        @break

        @case('members_component')
            <!-- Project Members Section -->
            <a href="{{ route('projectMembers.show', ['project' => $project->id]) }}" class="view-all-link">Members</a>
            <div class="members-list">
                @foreach ($projectMembers as $member)
                    <div class="member-item">
                        <img src="{{ $member->getAccountImage() }}" alt="{{ $member->name }}" class="member-photo">
                        <p
                            onclick="window.location='{{ route('memberAccount.show', ['project' => $project->id, 'member' => $member->id]) }}'">
                            {{ $member->name }}</p>
                    </div>
                @endforeach
                @if ($projectMembers->count() == 30)
                    <a href="{{ route('projectMembers.show', ['project' => $project->id]) }}" class="view-all-link">See all</a>
                @endif
            </div>
        @break

        @case('analytics_component')
            <!-- Analytics Section -->
            <a href="{{ route('project.timeline', ['project' => $project->id]) }}" class="view-all-link">History</a>
            <div class="timeline-component">
                @include('partials.projectEvents')
                @if ($projectEvents->count() == 15)
                    <a href="{{ route('project.timeline', ['project' => $project->id]) }}" class="view-all-link">See all</a>
                @endif
            </div>
        @break

        @case('productivity_component')
            <!-- Productivity Section -->
            <script src="{{ asset('js/productivityComponent.js') }}" defer></script>
            <a href="{{ route('project.table.show', ['project' => $project->id]) }}" class="view-all-link">Productivity</a>
            <div class="productivity-component">
                <table id ="project-tasks-table" class="sidebar-tasks-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="project-tasks-sortable-header" data-sort-key="deadline">
                                Deadline
                                <div class="tasks-sort-control">
                                    <span class="tasks-sort-icon"></span>
                                    <button class="tasks-dropdown-button tooltip left" data-text="Sort">⋮</button>
                                    <div class="tasks-dropdown-menu" style="display: none;">
                                        <ul class="dropdown-content">
                                            <li data-sort-order="asc" class="focusable">
                                                Sort Ascending
                                                <span class="tasks-remove-selection focusable" style="display: none;">×</span>
                                            </li>
                                            <li data-sort-order="desc" class="focusable">
                                                Sort Descending
                                                <span class="tasks-remove-selection focusable" style="display: none;">×</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                            <th class="project-tasks-sortable-header" data-sort-key="priority">
                                Priority
                                <div class="tasks-sort-control">
                                    <span class="tasks-sort-icon"></span>
                                    <button class="tasks-dropdown-button tooltip left" data-text="Sort">⋮</button>
                                    <div class="tasks-dropdown-menu" style="display: none;">
                                        <ul class="dropdown-content">
                                            <li data-sort-order="asc" class="focusable">
                                                Sort Ascending
                                                <span class="tasks-remove-selection focusable" style="display: none;">×</span>
                                            </li>
                                            <li data-sort-order="desc" class="focusable">
                                                Sort Descending
                                                <span class="tasks-remove-selection focusable" style="display: none;">×</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($assignedTasks->isEmpty())
                            <tr>
                                <td colspan = "3">
                                    <p class="no-invites">No tasks found for this project.</p>
                                </td>
                            </tr>
                        @endif
                        @foreach ($assignedTasks as $task)
                            <tr class="sidebar-task-item">
                                <td>
                                    <a href="#" class="task-link" data-id="{{ $task->id }}">
                                        {{ $task->name }}
                                    </a>
                                </td>
                                <td>
                                    @if ($task->deadline_date)
                                        {{ \Carbon\Carbon::parse($task->deadline_date)->format('d M Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if ($task->priority->value === 'High')
                                        <i class="fa-solid fa-flag priority-high"></i> High
                                    @elseif($task->priority->value === 'Medium')
                                        <i class="fa-solid fa-flag priority-medium"></i> Medium
                                    @elseif($task->priority->value === 'Low')
                                        <i class="fa-solid fa-flag priority-low"></i> Low
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($assignedTasks->count() == 15)
                    <a href="{{ route('project.table.show', ['project' => $project->id]) }}" class="view-all-link">See all</a>
                @endif
            </div>
        @break

    @endswitch
</div>
