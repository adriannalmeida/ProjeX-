<div id="projects-tab-content" class="tab-content.active relative">
    <div class="search-header-bar header-bar">
        @include('partials.searchBar', [
            'formAction' => route('admin.projects'),
            'placeholder' => 'Search projects...',
            'search' => request()->input('search') ?? '',
            'resultsUrl' => route('admin.projects.search.ajax'),
            'results' => $projects->map(
                fn($project) => [
                    'url' => route('project.show', ['project' => $project->id]),
                    'name' => $project->name,
                    'description' => $project->description,
                ]),
        ])
    </div>

    <div class="project-container">
        <table id="all_projects">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Title</th>
                    <th class="hide-tablet">Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $key => $project)
                    <tr>
                        <td>{{ $projects->firstItem() + $key }}</td>
                        <td>{{ $project->name }}</td>
                        <td class="hide-tablet">{{ Str::limit($project->description, 50) }}</td>
                        <td>
                            <form action="{{ route('project.show', $project->id) }}" method="GET"
                                style="display:inline;">
                                <button type="submit" class="iconB">
                                    <i class="fas fa-info-circle"></i><span>Details</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $projects->links() }}
        </div>
    </div>
</div>
