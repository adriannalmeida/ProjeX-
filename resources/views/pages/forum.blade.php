@extends('layouts.app')

@section('content')
    <script src="{{ asset('js/forum.js') }}" defer></script>

    <section class="forum-page">
        <h2 class="page-title">Forum Messages</h2>
        <div class="project-container">
            <div class="forum-details">
                @include('partials.forumMessages', ['messages' => $forumMessages])
            </div>
            @if ($isMember)
                <div class="forum-form">
                    <form id="forumMessageForm" method="POST" action="{{ route('forum.store', ['project' => $project->id]) }}"
                        data-account-id="{{ Auth::user()->id }}">
                        @csrf
                        <textarea name="message" id="messageContent" rows="4" placeholder="Type your message here..." required></textarea>
                        <button type="submit">Send</button>
                    </form>
                </div>
            @endif
        </div>
    </section>
    @include('partials.sidebar', ['project' => $project, 'page' => 'forum'])
@endsection
