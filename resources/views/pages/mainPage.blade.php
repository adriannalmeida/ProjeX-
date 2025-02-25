@extends('layouts.app')

@section('content')
    <div class="project-intro">
        <div class=intro-text>
            <h1>Take Control of Your Projects with ProjeX</h1>
            <p>Collaborate effortlessly, track progress in real-time, and streamline your workflow all in one platform.</p>
            <ul class="features">
                <li>Efficient team collaboration</li>
                <li>Real-time notifications and project tracking</li>
                <li>Personalized workspace and advanced tools</li>
            </ul>
            <div class="button-container">
                <form action="/login" method="GET">
                    <button type="submit" class="btn btn-primary">Get Started Now</button>
                </form>
            </div>
        </div>

        <div class="intro-image">
            <img src="{{ asset('assets/projex_logo.png') }}" alt="ProjeX - Manage Your Projects" class="img-fluid">
        </div>

    </div>
@endsection
