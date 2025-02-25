<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Project;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProjectPolicy
{

    /**
     * Determine whether the user has member access to the project.
     * Ensures the project is not archived and the user is a member.
     */
    public function memberAccess(Account $account, Project $project){
        if ($project->archived) {
            return false;
        }
        return ($project->members->contains('id', $account->id));
    }

    /**
     * Determine whether the user has member access to the project, but can only view it.
     * Ensures the project is archived and the user is a member.
     */
    public function memberOnlyViewAccess(Account $account, Project $project){
        return ($project->archived && $project->members->contains('id', $account->id));
    }

    /**
     * Determine whether the user has public access to the project.
     * Allows access if the project is archived, public, or the user has member or admin permissions.
     */
    public function publicAccess(Account $account, Project $project){
        return ($project->ispublic || $this->memberAccess($account, $project) || $account->admin);
    }

    /**
     * Determine whether the user has coordinator access to the project.
     * Ensures the project is not archived and the user is the project coordinator.
     */
    public function coordinatorAccess(Account $account, Project $project){
        if ($project->archived) {
            return false;
        }
        return ($project->project_coordinator_id === $account->id);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, Project $project): bool
    {
        return $this->publicAccess($account, $project) || $this->memberOnlyViewAccess($account, $project);
    }

    /**
     * Determine whether the user can interact the model.
     */
    public function interact(Account $account, Project $project): bool
    {
        return $this->memberAccess($account, $project);
    }

    /**
     * Determine whether the user can create Task Table models.
     */
    public function createTaskTable(Account $account, Project $project): bool
    {
        return ($this->coordinatorAccess($account, $project) || ($project->create_tasktable_permission && $project->members->contains($account->id)));
    }

    /**
     * Determine whether the user can update the model (including update Tasks).
     */
    public function update(Account $account, Project $project): bool
    {

        return (($project->create_task_permission && $this->memberAccess($account, $project)) || $this->coordinatorAccess($account, $project));
    }

    /**
     * Determine whether the user can send an invitation
     */
    public function sendInvite(Account $account, Project $project): bool
    {
        return ($this->coordinatorAccess($account, $project) || ($project->add_member_permission && $project->members->contains($account->id)));
    }

    /**
     * Determine whether the user can remove another from the project
     */
    public function removeAccount(Account $account, Project $project): bool
    {
        return $this->coordinatorAccess($account, $project);
    }

    /**
     * Determine whether the user can leave the project
     */
    public function leaveProject(Account $account, Project $project): bool
    {
        return ($project->members->contains('id', $account->id));
    }

    /**
     * Determine whether the user can change the project coordinator
     */
    public function changeCoordinator(Account $account, Project $project): bool
    {
        return $this->coordinatorAccess($account, $project) && ($project->members->contains('id', request()->account));
    }

    /**
     * Determine whether the user can change the project description
     */
    public function changeDescription(Account $account, Project $project): bool
    {
        return $this->coordinatorAccess($account, $project);
    }

    /**
     * Determine whether the user can change project permissions.
     */
    public function changePermissions(Account $account, Project $project): bool
    {
        return $this->coordinatorAccess($account, $project);
    }

    /**
     * Determine whether the user can update the project's privacy settings.
     */
    public function updatePrivacy(Account $account, Project $project): bool
    {
        return $this->coordinatorAccess($account, $project);
    }

    /**
     * Determine whether the user can post a message in the forum
     */
    public function postMessage(Account $account, Project $project): bool
    {
        return $this->memberAccess($account, $project);
    }

    /**
     * Determine if the account can create a task.
     */
    public function createTask(Account $account, Project $project)
    {
        return (($project->create_task_permission && $project->members->contains($account->id)) || $this->coordinatorAccess);
    }

     /**
     * Determine if different components are in different layout positions
     */
    public function validateComponents(Account $user, Project $project, array $components)
    {
        $filteredComponents = array_filter($components, function ($component) {
            return $component !== 'none';
        });

        return count($filteredComponents) === count(array_unique($filteredComponents));
    }

}
