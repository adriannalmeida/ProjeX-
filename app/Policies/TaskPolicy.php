<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Task;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class TaskPolicy

{
    /**
     * Determine if the account has coordinator access for the task.
     * Checks if the account is the coordinator of the project associated with the task's task table.
     */
    public function coordinatorAccess(Account $account, Task $task){
        $coordinator = $task->taskTable->getProject->coordinator;
        return ($coordinator->id=== $account->id);
    }

    /**
     * Determine whether the account can view the model.
     */
    public function view(Account $account, Task $task)
    {
        return ($task->taskTable->getProject->members->contains($account->id) || $task->taskTable->getProject->ispublic || $task->taskTable->getProject->archived || $account->admin);
    }

    /**
     * Determine if the account can update the task.
     */
    public function update(Account $account, Task $task)
    {   
        $project = $task->taskTable->getProject;
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && (($project->edit_task_permission && $project->members->contains($account->id)) || $this->coordinatorAccess($account, $task)));
    }

    /**
     * Determine if the account can add a deadline to the task.
     * Allows this action if the task is not deleted and the account has the necessary permissions or coordinator access.
     */
    public function addDeadline(Account $account, Task $task)
    {
        $project = $task->taskTable->getProject;
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && (($project->add_deadline_permission && $project->members->contains($account->id)) || $this->coordinatorAccess($account, $task)));
    }   

    /**
     * Determine if the account can delete the task.
     */
    public function delete(Account $account, Task $task)
    {
        $project = $task->taskTable->getProject;
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && (($project->edit_task_permission && $project->members->contains($account->id)) || $this->coordinatorAccess($account, $task)));
    }

    /**
     * Determine if the account can mark the task as completed.
     */
    public function completed(Account $account, Task $task)
    {
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && ($task->taskTable->getProject->members->contains($account->id)));
    }

    /**
     * Determine if a user can assign another user to a task
     */
    public function assign(Account $account, Task $task){
        $project = $task->taskTable->getProject;
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && (($project->assign_tasks_permission && $project->members->contains($account->id)) || $this->coordinatorAccess($account, $task)));
    }
    /**
     * Determine if the account can mark the task as uncompleted.
     */
    public function uncompleted(Account $account, Task $task)
    {
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && ($task->taskTable->getProject->members->contains($account->id)));
    }

    /**
     * Determine if the account can change the position of a task.
     */
    public function changePosition(Account $account, Task $task)
    {
        return ($task->taskTable->getProject->members->contains($account->id));
    }


    /**
     * Determine if the account can change a task's accounts assignment
     */
    public function changeAccountAssignment(Account $account, Task $task)
    {
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && ($task->taskTable->getProject->members->contains($account->id)));
    }

    /**
     * Determine if the account can post a comment to a task
     */
    public function postComment(Account $account, Task $task)
    {
        $isTaskDeleted = $task->taskTable->position === 0;
        return (!$isTaskDeleted && ($task->taskTable->getProject->members->contains($account->id)));
    }

}
