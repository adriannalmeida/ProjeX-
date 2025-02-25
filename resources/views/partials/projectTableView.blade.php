<div class="relative">
    <div class="project-container">
        <div class="project-tasks-management">
            @if (empty($taskTables))
                <p>No tasks found for this project.</p>
            @else
                <table id="project-tasks-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th></th>
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
                                            <li data-sort-order="asc" class="focusable">
                                                Sort Ascending
                                                <span class="tasks-remove-selection focusable"
                                                    style="display: none;">×</span>
                                            </li>
                                            <li data-sort-order="desc" class="focusable">
                                                Sort Descending
                                                <span class="tasks-remove-selection focusable"
                                                    style="display: none;">×</span>
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
                                                <span class="tasks-remove-selection focusable"
                                                    style="display: none;">×</span>
                                            </li>
                                            <li data-sort-order="desc" class="focusable">
                                                Sort Descending
                                                <span class="tasks-remove-selection focusable"
                                                    style="display: none;">×</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $globalIndex = 1; @endphp
                        @foreach ($tasksPaginated as $task)
                            <tr class="project-tasks-item" data-task-id="{{ $task->id }}">
                                <td>{{ $tasksPaginated->perPage() * ($tasksPaginated->currentPage() - 1) + $globalIndex }}
                                </td>
                                <td></td>
                                <td>
                                    <a href="#" class="task-link" data-id="{{ $task->id }}">
                                        {{ $task->name }}
                                    </a>
                                </td>
                                <td class="hide-tablet">{{ $task->description }}</td>
                                <td class="hide-tablet">{{ \Carbon\Carbon::parse($task->start_date)->format('d M Y') }}
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
                            @php $globalIndex++; @endphp
                        @endforeach
                        <tr class="summary-row">
                            <td></td>
                            <td></td>
                            <td></td>
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
                    {{ $tasksPaginated->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
