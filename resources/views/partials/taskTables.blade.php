<section id="project-middle">
    <div id="taskTables">
        @forelse ($taskTables as $taskTable)
            <div class="taskTable">
                <h4>{{ $taskTable->name }}</h4>
                @if (!$project->archived && $isCoordinator)
                    <form action="/taskTables/{{ $taskTable->id }}" method="POST" class="remove-task-table confirmation">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @method('DELETE')
                        <div class="tooltip left" data-text="Delete task table">
                            <button type="submit" class="icon-button confirm-action">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </form>
                @endif
                <ol class="{{ $isMember ? 'taskList' : 'taskListView' }}">
                    @forelse ($taskTable->tasks as $task)
                        <li class="task {{ $task->finish_date ? 'completed' : '' }}">
                            <a href="#" class="task-link" data-id="{{ $task->id }}">
                                <div class="taskHead">
                                    <p class="taskName">{{ $task->name }}</p>
                                    <p class="taskPriority {{ $task->finish_date ? 'Completed' : $task->priority }}">
                                        {{ $task->finish_date ? 'Completed' : $task->priority }}</p>
                                </div>
                                @if (!$task->finish_date)
                                    <div class="taskBody">
                                        <p>{{ $task->description }}</p>
                                    </div>
                                @endif
                                <div class="taskFooter">
                                    <p class="date {{ $task->finish_date ? 'finished' : '' }}">
                                        {{ $task->finish_date ? $task->finish_date->format('Y-m-d') : ($task->deadline_date ? $task->deadline_date->format('Y-m-d') : 'No deadline') }}
                                    </p>
                                    @if (!$task->finish_date)
                                        <p class="accounts">{{ $task->accounts->count() }} <i class="fa fa-user"></i>
                                        </p>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="table-empty">
                            <p>No tasks available in this table.</p>
                        </li>
                    @endforelse
                </ol>
                @if (!$project->archived && ($isCoordinator || ($project->create_task_permission && $isMember)))
                    <div class="add-task">
                        <a href="#" class="taskTable-link tooltip left" data-text="Create new task"
                            data-id="{{ $taskTable->id }}">+</a>
                    </div>
                @endif
            </div>
        @empty
        @endforelse
    </div>
    @if ($taskTables->isEmpty())
        <p>No task tables available for this project.</p>
        @if (!$project->archived && ($isCoordinator || ($project->create_tasktable_permission && $isMember)))
            <div class="openCreateTaskTable">
                <a href="#" id="create-first-task-table" class="create-task-table">Create task table</a>
            </div>
        @endif
    @else
        @if (!$project->archived && ($isCoordinator || ($project->create_tasktable_permission && $isMember)))
            <div class="openCreateTaskTable">
                <a href="#" id="create-task-table" class="create-task-table tooltip left"
                    data-text="Create task table">+</a>
            </div>
        @endif
    @endif



    @if (!$project->archived && ($isCoordinator || ($project->create_task_permission && $isMember)))
        <!-- Create Task Modal -->
        <div id="createModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Create New Task</h2>
                    <div class="modal-actions">
                        <button class="icon-button close-modal" id="closeCreateModal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <form id="createTaskForm" action="#" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @method('POST')
                        <div class="task-attribute">
                            <label for="createTaskName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="createTaskName" name="name" required>
                        </div>

                        <div class="task-attribute">
                            <label for="createTaskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="createTaskDescription" name="description" style="resize:none"></textarea>
                        </div>

                        @if ($isCoordinator || $project->add_deadline_permission)
                            <div class="task-attribute">
                                <label for="createTaskDeadlineDate" class="form-label">Deadline</label>
                                <input type="date" class="form-control" id="createTaskDeadlineDate"
                                    name="deadline_date">
                            </div>
                        @endif

                        <div class="task-attribute">
                            <label for="createTaskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="createTaskPriority" name="priority" required>
                                <option value="" selected>Select an Option</option>
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Task</button>
                    </form>
                </div>
            </div>
        </div>
    @endif


    @if (!$project->archived && ($isCoordinator || ($project->create_tasktable_permission && $isMember)))
        <!-- Create Task Table Modal -->
        <div id="createTaskTableModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Create New Task Table</h2>
                    <div class="modal-actions">
                        <button class="icon-button close-modal" id="closeCreateTaskTableModal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <form id="createTaskTableForm"
                        action="{{ route('taskTable.store', ['project' => $project->id]) }}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <label for="createTaskTableName" class="form-label">Task Table Name</label>
                            <input type="text" class="form-control" id="createTaskTableName" name="name"
                                required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Task Table</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</section>
