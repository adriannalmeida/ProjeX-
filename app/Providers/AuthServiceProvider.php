<?php

namespace App\Providers;

use App\Models\AccountTask;
use App\Models\Invitation;
use App\Models\Project;
use App\Policies\InvitationPolicy;
use App\Policies\ProjectPolicy;
use App\Models\Task;
use App\Policies\TaskPolicy;
use App\Policies\AdminPolicy;
use App\Models\Account;


// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Task::class => TaskPolicy::class,
        Project::class => ProjectPolicy::class,
        Invitation::class => InvitationPolicy::class,
        Account::class => AdminPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
