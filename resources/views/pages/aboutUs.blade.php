@extends('layouts.app')
@section('content')
    <div class="aboutUs">

        <section class="intro">
            <h1>About ProjeX</h1>
            <div class="intro-image">
                <img src="{{ asset('assets/img2.jpeg') }}" alt="ProjeX logo">
            </div>

        </section>

        <div class="intro-text">
            <p>
                <strong>ProjeX</strong> is a web-based project management system created by a dedicated team of developers
                who sought to overcome the limitations of existing tools.
                <br>Designed with flexibility and productivity in mind, <strong>ProjeX</strong> delivers an intuitive
                platform helping coordinate projects, organize tasks, and maintain clear communication.
            </p>
        </div>

        <section class="project-container">
            <h2>Our Mission</h2>
            <p>
                ProjeX was built to simplify project management through an easy-to-use interface.
                We deliver a robust functionality for diverse teams.<br> By focusing on adaptive design and role-based
                access, ProjeX empowers users with the right tools to streamline workflows and enhance collaboration.
            </p>
        </section>

        <section class="project-container">
            <h2>Key Features</h2>
            <ul>
                <li><strong> User-Friendly Experience:</strong> Adaptive design for seamless use across all devices with a
                    clean, intuitive interface.</li>

                <li><strong>Comprehensive Project Tools:</strong> ProjeX provides all the essential tools for smooth project
                    management
                    <ul>
                        <li> Create and assign tasks with ease</li>
                        <li> Set priorities and deadlines for effective task tracking</li>
                        <li> Stay connected with real-time notifications and an integrated project chat system</li>
                    </ul>
                </li>
                <li><strong> Public Projects:</strong> ProjeX offers the flexibility to create public projects available to
                    everyone,a feature perfect for open-source initiatives.</li>
                <li><strong> Platform Security and Oversight: </strong>Platform security is a top priority. Administrators
                    may inspect users conduct across the system and restrict accounts under appropriate circumstances.</li>

                <li><strong>Comprehensive Project Tools:</strong> Task creation, assignment, notifications, and project chat
                    for enhanced communication.</li>
            </ul>
        </section>
    </div>
@endsection
