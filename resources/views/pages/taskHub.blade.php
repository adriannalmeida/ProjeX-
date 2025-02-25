@extends('layouts.app')

@section('content')
    <div class="header-bar">
        <a href="{{ route('projects') }}" class="back-arrow"><i class="fas fa-arrow-left"></i>
            <h3>Projects</h3>
        </a>
    </div>
    <section id="suggestions-page" class="relative">
        <h2 class="page-title">Task Hub</h2>
        <div class="project-container">
            @include('partials.tasksPrioritization')
        </div>
    </section>
@endsection
