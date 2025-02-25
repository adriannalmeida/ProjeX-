@extends('layouts.app')

@section('content')
    <section id="timeline-page">
        <h2 class="page-title">Project Timeline</h2>
        <div class="project-container">
            <div class="chart-container">
                {!! $chart->render() !!}
            </div>
            <div class="timeline-details">
                @include('partials.projectEvents', compact('projectEvents', 'project'))
            </div>
            <div id="pag_projectEvents" class="pagination">
                {{ $projectEvents->links() }}
            </div>
        </div>
    </section>
    @include('partials.sidebar', ['project' => $project, 'page' => 'history'])
@endsection
