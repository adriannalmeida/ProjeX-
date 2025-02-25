<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;
use App\Models\TaskTable;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

/**
 * Defines an enumeration for different types of project events.
 * These types are used to categorize events related to tasks.
 */
enum EventType: string {
    case Task_Created = 'Task_Created';
    case Task_Completed = 'Task_Completed';
    case Task_Priority_Changed = 'Task_Priority_Changed';
    case Task_Deactivated = 'Task_Deactivated';
    case Task_Assigned = 'Task_Assigned';
    case Task_Unassigned = 'Task_Unassigned';
}

class ProjectEvent extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'project_event';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account',
        'task',
        'time',
        'event_type',
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time' => 'datetime',
        'event_type' => EventType::class,
    ];

    /**
     * Defines a many-to-one relationship between ProjectEvent and Account.
     * Associates this project event with the account that triggered it.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account');
    }

    /**
     * Defines a many-to-one relationship between ProjectEvent and Task.
     * Associates this project event with the related task.
     */
    public function getTask()
    {
        return $this->belongsTo(Task::class, 'task');
    }

    /**
     * Defines a one-to-many relationship between ProjectEvent and Notification.
     * Retrieves all notifications associated with this project event.
     */
    public function notifications()
    {
        return $this->hasMany(
            Notification::class,
            'project_event',
            'id',
        );
    }

    /**
     * Retrieves the project associated with this project event.
     * Navigates through the related task and its task table to fetch the project.
     *
     * @return mixed|null The project associated with the event or null if not found.
     */
    public function project()
    {
        $task = $this->getTask()->first();
        if ($task && $task->taskTable) {
            return $task->taskTable->getProject();
        }

        return null;
    }

    /**
     * Retrieves the name of the account associated with this event, formatted for display.
     *
     * @return string The account name in the format '@username' or 'Unknown Account' if not found.
     */
    public function accountInDescription(): string
    {
        $account = $this->account()->first();
        $accountName = '@' . ($account->username ?? 'Unknown Account');
        return $accountName;
    }

    /**
     * Retrieves the route to the account's profile page.
     *
     * @return string The URL to the account's profile or an empty string if not available.
     */
    public function accountRoute(): string
    {
        $account = $this->account()->first();
        if ($account->id) {
            return route('memberAccount.show', ['project' => $this->project->id, 'member' => $account->id]);
        }
        return "";
    }

    /**
     * Generates a human-readable description of the project event based on its type.
     *
     * @return string The event description, formatted with task name and event details.
     */
    public function description(): string
    {
        $taskName = $this->getTask->name ?? 'Unknown Task';

        switch ($this->event_type) {
            case EventType::Task_Created:
                return " created task \"$taskName\"";

            case EventType::Task_Completed:
                return " marked task \"$taskName\" as completed";

            case EventType::Task_Priority_Changed:
                return " changed the priority of task \"$taskName\"";

            case EventType::Task_Deactivated:
                return " deactivated task \"$taskName\"";

            case EventType::Task_Assigned:
                return " was assigned to task \"$taskName\"";

            case EventType::Task_Unassigned:
                return " was unassigned from task \"$taskName\"";

            default:
                return "An event occurred for task \"$taskName\"";
        }
    }

    /**
     * Retrieves the type of the event as a string.
     *
     * @return string The name of the event type or 'Unknown' if not available.
     */
    public function type(): string
    {
        $type = $this->event_type->name ?? 'Unknown';
        return "$type";
    }

}
