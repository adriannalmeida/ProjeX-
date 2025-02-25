@php use Carbon\Carbon; @endphp
<script src="{{ asset('js/taskManagement.js') }}" defer></script>
<div class="project-tasks-management">
    @if (empty($assignedTasksPaginated))
        <p>No tasks found for this project.</p>
    @else
        <table id="project-tasks-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Project</th>
                    <th>Name</th>
                    <th class="hide-tablet">Description</th>
                    <th class="hide-tablet">Start Date</th>
                    <th class="project-tasks-sortable-header" data-sort-key="deadline">
                        Deadline
                        <div class="tasks-sort-control">
                            <span class="tasks-sort-icon"></span>
                            <button class="tasks-dropdown-button tooltip left" data-text="Sort">⋮</button>
                            <div class="tasks-dropdown-menu" style="display: none;">
                                <ul class="dropdown-content">
                                    <li data-sort-order="asc">
                                        Sort Ascending
                                        <span class="tasks-remove-selection focusable" style="display: none;">×</span>
                                    </li>
                                    <li data-sort-order="desc">
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
                                    <li data-sort-order="asc">
                                        Sort Ascending
                                        <span class="tasks-remove-selection focusable" style="display: none;">×</span>
                                    </li>
                                    <li data-sort-order="desc">
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
                @foreach ($assignedTasksPaginated as $index => $task)
                    <tr class="project-tasks-item" data-task-id="{{ $task->id }}">
                        <td>{{ $assignedTasksPaginated->perPage() * ($assignedTasksPaginated->currentPage() - 1) + $index + 1 }}
                        </td>
                        <td>
                            @if ($task->getProject())
                                <a href="{{ route('project.show', ['project' => $task->getProject()->id]) }}"
                                    class="table-project-link">
                                    {{ $task->getProject()->name }}
                                </a>
                            @else
                                <span class="table-project-link">Unknown Project</span>
                            @endif
                        </td>
                        <td>
                            @if ($task->id)
                                <a href="{{ route('project.task.show', ['project' => $task->getProject()->id, 'task' => $task->id]) }}"
                                    class="table-task-link ">
                                    {{ $task->name }}
                                </a>
                            @else
                                <span class="table-task-link">Unnamed Task</span>
                            @endif
                        </td>
                        <td class="hide-tablet">{{ $task->description }}</td>
                        <td class="hide-tablet">{{ Carbon::parse($task->start_date)->format('d M Y') }}</td>
                        <td>
                            @if ($task->deadline_date)
                                {{ Carbon::parse($task->deadline_date)->format('d M Y') }}
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
                <tr class="summary-row">
                    <td></td>
                    <td></td>
                    <td>{{ $projectCount }} {{ $projectCount == 1 ? 'project' : 'projects' }}</td>
                    <td class="hide-tablet"></td>
                    <td class="hide-tablet">{{ $startDateRange }}</td>
                    <td>{{ $deadlineRange }}</td>
                    <td>
                        <i class="fa-solid fa-flag priority-high"></i> {{ $priorityCounts['High'] }}
                        <i class="fa-solid fa-flag priority-medium"></i> {{ $priorityCounts['Medium'] }}
                        <i class="fa-solid fa-flag priority-low"></i> {{ $priorityCounts['Low'] }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div id="pag_tasks" class="pagination">
            {{ $assignedTasksPaginated->links() }}
        </div>
    @endif
</div>
