@extends('layouts.app')
@section('title', 'Project Details')
@section('content')
    <script src={{ asset('js/search.js') }} defer></script>
    <script src="{{ asset('js/modal.js') }}" defer></script>
    <script src="{{ asset('js/taskSearch.js') }}" defer></script>
    <script src="{{ asset('js/task.js') }}" defer></script>
    <script src="{{ asset('js/project.js') }}" defer></script>
    <script src="{{ asset('js/deleteTasktable.js') }}" defer></script>

    @if (!$project->archived && $isCoordinator)
        <script src="{{ asset('js/createTaskTable.js') }}" defer></script>
    @endif

    <div id="search-header-bar" class="search-header-bar header-bar">
        <div class="left-section">
            <a href="{{ route('projects') }}" class="back-arrow"><i class="fas fa-arrow-left"></i>
                <h3>Projects</h3>
            </a>
        </div>
        @include('partials.searchBar', [
            'formAction' => $tableView
                ? route('project.table.show', ['project' => $project->id])
                : route('project.show', ['project' => $project->id]),
            'placeholder' => 'Search tasks...',
            'search' => $search ?? '',
            'filters' => ['All Priorities', 'High', 'Medium', 'Low'],
            'currentFilter' => $filter ?? '',
            'resultsUrl' => route('task.search.ajax', ['project' => $project]),
            'results' => $taskTables->flatMap(
                fn($taskTable) => $taskTable->tasks->map(fn($task) => [
                        'url' => '#',
                        'name' => $task->name,
                        'description' => $task->description,
                        'class' => 'task-link',
                        'id' => $task->id,
                    ])),
        ])

    </div>


    @if ($tableView)
        <script src="{{ asset('js/taskManagement.js') }}" defer></script>

        @include('partials.sidebar', ['project' => $project, 'page' => 'projectTable'])
        @include('partials.projectTableView')

        <div class="task-modal">
            @include('partials.taskPanel', compact('isMember', 'project'))
        </div>
    @else
        @include('partials.sidebar', ['project' => $project, 'page' => 'project'])
        <div id="main-content" class="project-details">
            <aside id="left-components">
                @if ($isMember)
                    @if ($left_top != null)
                        @include('partials.component', [
                            'component' => $left_top,
                            $project,
                            $projectMembers,
                            $forumMessages,
                            $projectEvents,
                            $assignedTasks,
                        ])
                    @endif
                    @if ($left_bottom != null)
                        @include('partials.component', [
                            'component' => $left_bottom,
                            $project,
                            $projectMembers,
                            $forumMessages,
                            $projectEvents,
                            $assignedTasks,
                        ])
                    @endif
                @endif
            </aside>
            @include('partials.taskTables')
            <div class="task-modal">
                @include('partials.taskPanel', compact('isMember', 'project'))
            </div>
            <aside id="right-components">
                @if ($isMember)
                    @if ($right_top != null)
                        @include('partials.component', [
                            'component' => $right_top,
                            $project,
                            $projectMembers,
                            $forumMessages,
                            $projectEvents,
                            $assignedTasks,
                        ])
                    @endif
                    @if ($right_bottom != null)
                        @include('partials.component', [
                            'component' => $right_bottom,
                            $project,
                            $projectMembers,
                            $forumMessages,
                            $projectEvents,
                            $assignedTasks,
                        ])
                    @endif
                @endif
            </aside>
        </div>
    @endif
@endsection
