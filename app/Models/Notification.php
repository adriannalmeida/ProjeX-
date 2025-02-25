<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

enum NotificationType: string {
    case Coordinator_Change = 'Coordinator_Change';
    case Accepted_Invite = 'Accepted_Invite';
    case Task_Completed = 'Task_Completed';
    case Assigned_Task = 'Assigned_Task';
}

class Notification extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'notification';
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'create_date',
        'viewed',
        'emitted_to',
        'project',
        'project_event',
        'checked',
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'create_date' => 'datetime',
        'type' => NotificationType::class,
        'checked' => 'boolean',
    ];

    
    /**
     * Retrieves the project associated with this notification.
     * - If the 'project' property is not null, it establishes a belongsTo relationship with Project.
     * - Otherwise, it attempts to retrieve the project via the related ProjectEvent.
     * Returns null if no associated project is found.
     */
    public function getProject()
    {
        if (!is_null($this->project)) {
            return $this->belongsTo(Project::class, 'project');
        }

        if ($this->project_event) {
            $projectEvent = $this->projectEvent()->first();

            return $projectEvent ? $projectEvent->project() : null;
        }
        return null;
    }

    /**
     * Defines a many-to-one relationship between Notification and ProjectEvent.
     * Associates this notification with a single project event.
     */
    public function projectEvent()
    {
        return $this->belongsTo(ProjectEvent::class, 'project_event');
    }

    /**
     * Defines a many-to-one relationship between Notification and Account.
     * Associates this notification with the account to which it was emitted.
     */
    public function emittedTo()
    {
        return $this->belongsTo(Account::class, 'emitted_to');
    }

    /**
     * Generates a human-readable description for the notification based on its type.
     * Uses the type of the notification to determine the appropriate message.
     * Supports cases such as Coordinator Change, Accepted Invite, Task Completion, and Assigned Task.
     * If no specific case matches, returns a default notification message.
     *
     * @return string The description of the notification.
     */
    public function description(): string
    {
        switch ($this->type) {
            case NotificationType::Coordinator_Change:
                $project = $this->getProject;
                return $project
                    ? "Coordinator has changed in project '{$project->name}'."
                    :  "The project coordinator has been changed.";

            case NotificationType::Accepted_Invite:
                $project = $this->getProject;
                return $project
                    ? "A new member joined the project '{$project->name}'."
                    : "A new member joined a project.";

            case NotificationType::Task_Completed:
                $task = $this->projectEvent->getTask;
                return $task
                    ? "The task '{$task->name}' has been completed in project '{$this->getProject->name}'."
                    : "A task has been completed.";

            case NotificationType::Assigned_Task:
                $task = $this->projectEvent->getTask;
                return $task
                    ? "You were assigned to task '{$task->name}' in project '{$this->getProject->name}'."
                    : "A task has been assigned.";

            default:
                return "Notification received.";
        }
    }

}

