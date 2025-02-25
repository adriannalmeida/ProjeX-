<div id="taskPanel" class="panel">
    <div id="content-task-details" class="panel-content">
        <section class="panel-header">
            <h2 class="taskTitleModal">Task Title</h2>
            <div class="panel-actions">
                @if (!$project->archived && ($isCoordinator || ($project->edit_task_permission && $isMember)))
                    <a href="#" id="editTaskLink" data-text="Edit" class="icon-button tooltip left">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <form id="deleteTaskForm" action="#" method="POST" class="confirmation"
                        style="display:inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="icon-button confirm-action tooltip left" data-text="Delete Task">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                @endif
                <button class="icon-button close-panel tooltip left" data-text="Close Panel" id="closePanel">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <p class="taskPriority High"><span id="taskPriorityPanel">High</span></p>
            <p class="startDate date"><strong>Start Date:</strong> <span id="taskStartDatePanel"></span></p>
        </section>
        <div class="details-panel-body">
            <!-- Task Details Section -->
            <div id="details">
                <p class="description"><span id="taskDescriptionPanel"></span></p>
                <p class="date"><strong>Deadline Date:</strong> <span id="taskDeadlineDatePanel"></span></p>
                <p class="date"><strong>Finish Date:</strong> <span id="taskFinishDatePanel"></span></p>
            </div>

            <!-- Assignees Section -->
            <div id="panel-right">
                <section id="assignees">
                    <h6>
                        <strong>Assignees:</strong>
                        @if (!$project->archived && ($isCoordinator || ($project->assign_tasks_permission && $isMember)))
                            <span class="icon-button focusable tooltip left" data-text="Assign User"
                                id="toggleAssignIcon">
                                <i class="fa fa-user-plus" id="openAssignIcon"></i>
                                <i class="fa fa-times hidden" id="closeAssignIcon"></i>
                            </span>
                        @endif
                    </h6>
                    <div id="taskAssigneesPanel" class="assignees-list"></div>
                    @if (!$project->archived && ($isCoordinator || ($project->assign_tasks_permission && $isMember)))
                        <div class="search-bar-container">
                            <input type="text" id="userSearch" placeholder="Search users..." class="search-bar"
                                style="display: none;">
                            <span id="clearSearch" class="clear-search focusable" style="display: none;">&times;</span>
                            <p id="noUsersMessage" style="display: none;">No users found</p>
                            <ul id="taskAssignPanel" class="usersList">
                            </ul>
                        </div>
                    @endif
                </section>
                <div id="button-complete">
                    @if (!$project->archived && $isMember)
                        <form id="markCompletedForm" action="#" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">Mark as Completed</button>
                        </form>
                        <form id="markUncompletedForm" action="#" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">Mark as Uncompleted</button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Task Events Section -->
            <section id="events">
                <h6><strong>Task Events:</strong></h6>
                <div class="events-scroll">
                    <ul class="list-group">
                        <!-- Dynamically populated -->
                    </ul>
                </div>
            </section>

            <!-- Comments Section -->
            <section id="comments">
                <h6><strong>Comments:</strong></h6>
                <div id="commentsContainer"></div>
                @if (!$project->archived && $isMember)
                    <div class="comment-form">
                        <form id="commentForm" method="POST" action="#">
                            @csrf
                            <textarea name="messageContent" id="messageContent" rows="4" placeholder="Type your comment here..." required></textarea>
                            <button type="submit">Comment</button>
                        </form>
                    </div>
                @endif
            </section>
        </div>
    </div>
    @if (!$project->archived && ($isCoordinator || ($project->edit_task_permission && $isMember)))
        <!-- Edit Task Modal -->
        <div id="content-task-edit" class="panel-content">
            <div class="panel-headerB">
                <div class= "backpanel">
                    <button class="icon-buttonB" id="backToTaskPanel">
                        <i class="fa fa-arrow-left"></i>
                    </button>
                </div>
                <h2>Edit Task</h2>
                <div class="panel-actions">
                    <button class="icon-button close-panel" id="closeEditModal">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <form id="editTaskForm" action="#" method="POST">
                    @csrf
                    @method('PUT')

                    <label for="editTaskName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="editTaskName" name="name" required>

                    <label for="editTaskDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="editTaskDescription" name="description" style="resize:none"></textarea>
                    @if ($isCoordinator || $project->add_deadline_permission)
                        <label for="editTaskDeadlineDate" class="form-label">Deadline</label>
                        <input type="date" class="form-control" id="editTaskDeadlineDate" name="deadline_date">
                    @endif

                    <label for="editTaskPriority" class="form-label">Priority</label>
                    <select class="form-select" id="editTaskPriority" name="priority" required>
                        <option value="" selected>Select an Option</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Update Task</button>
                </form>
            </div>
        </div>
    @endif
</div>
