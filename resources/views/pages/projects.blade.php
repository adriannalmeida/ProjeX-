@extends('layouts.app')
@section('title', 'Projects')
@section('content')
    <script src={{ asset('js/projectSearch.js') }} defer></script>
    <script src={{ asset('js/createProject.js') }} defer></script>
    <script src={{ asset('js/favouriteProject.js') }} defer></script>
    <script src="{{ asset('js/modal.js') }}" defer></script>
    <script src={{ asset('js/search.js') }} defer></script>
    <div class="search-header-bar header-bar search-project search-button-container">
        @include('partials.searchBar', [
            'formAction' => route('projects'),
            'placeholder' => 'Search projects...',
            'search' => $search ?? '',
            'resultsUrl' => route('projects.search.ajax'),
            'results' => $projects->map(
                fn($project) => [
                    'url' => route('project.show', ['project' => $project->id]),
                    'name' => $project->name,
                    'description' => $project->description,
                ]),
        ])

        <div id="createNewProject" class="search-btn">
            <button type="submit"><i class="fas fa-folder-plus"></i><span> Create Project</span></button>
        </div>
    </div>


    <div id="all-projects">
        <div id="tabs-container" data-url="{{ route('projects.tab.search.ajax') }}">
            <input type="radio" id="tabMy" name="tab" class="tab"
                {{ !$tab || $tab === 'tabMy' ? 'checked' : '' }}><label for="tabMy" class="focusable">My Projects</label>
            <input type="radio" id="tabFav" name="tab" class="tab"
                {{ $tab === 'tabFav' ? 'checked' : '' }}><label for="tabFav" class="focusable">Favourite</label>
            <input type="radio" id="tabPub" name="tab" class="tab"
                {{ $tab === 'tabPub' ? 'checked' : '' }}><label for="tabPub" class="focusable">Public Projects</label>
            <input type="radio" id="tabArchived" name="tab" class="tab"
                {{ $tab === 'tabArchived' ? 'checked' : '' }}><label for="tabArchived" class="focusable">Archived
                Projects</label>
        </div>
        <ul>
            <li class="tab-content">
                <div id="projectsSection">
                    @if (count($projects) > 0)
                        <table class="projects-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="hide-mobile">Description</th>
                                    <?php if ($tab !== 'tabArchived' && $tab !== 'tabPub'): ?>
                                    <th class="favourites">Favourite</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projects as $project)
                                    <tr data-href="/project/{{ $project->id }}" class="focusable">
                                        <td>
                                            <p class="project-name">{{ $project->name }}</p>
                                        </td>
                                        <td class="hide-mobile">
                                            <p class="project-description">
                                                {{ $project->description ? $project->description : '-' }}</p>
                                        </td>
                                        <?php if ($tab !== 'tabArchived' && $tab !== 'tabPub'): ?>
                                        <td>
                                            <div class="{{ isset($project->pivot) && $project->pivot->is_favourite ? 'favorite-project' : 'not-favorite-project' }}"
                                                data-id="{{ $project->id }}">
                                                <b class="centered focusable tooltip left"
                                                    data-text="{{ isset($project->pivot) && $project->pivot->is_favourite ? 'Remove from favourites' : 'Add to favourites' }}">★</b>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <h2>No projects available.</h2>
                    @endif
                </div>
                <div class="pagination">
                    {{ $projects->appends(request()->query())->links() }}
                </div>
            </li>
        </ul>
    </div>

    <!-- Create Project Modal -->
    <div id="createProjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create a New Project</h2>
                <div class="panel-actions">
                    <button class="icon-button close-modal" id="closeCreateProjectModal">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <form id="createProjectForm" action="{{ route('projects.store') }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label for="createProjectName">Project Name</label>
                        <input type="text" class="form-control" id="createProjectName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="createProjectDescription">Description</label>
                        <textarea class="form-control" id="createProjectDescription" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="createProjectIsPublic">Is Public</label>
                        <select class="form-control" id="createProjectIsPublic" name="isPublic" required>
                            <option value="">Select an option</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </form>
            </div>
        </div>
    </div>
@endsection
