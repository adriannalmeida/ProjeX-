<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Enum Priority
 * Represents task priority levels.
 */
enum Priority: string {
    case High = 'High';
    case Medium = 'Medium';
    case Low = 'Low';
}

class Task extends Model
{
    /**
     * Boot method for the Task model.
     * Includes model event listeners for saving and updating.
     */
    public static function boot()
    {
        parent::boot();

        // Prevent saving events from running when in console mode
        static::saving(function ($task) {
            if (app()->runningInConsole()) {
                return false;
            }
        });

        // Prevent updating events from running when in console mode
        static::updating(function ($task) {
            if (app()->runningInConsole()) {
                return false;
            }
        });
    }
    use HasFactory;
    public $timestamps = false;

    protected $table = 'task';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_table',
        'name',
        'description',
        'start_date',
        'deadline_date',
        'finish_date',
        'priority',
        'position',
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'finish_date' => 'date:Y-m-d',
        'deadline_date' => 'date:Y-m-d',
        'priority' => Priority::class
    ];

    /**
     * Defines a many-to-one relationship between Task and TaskTable.
     * Associates this task with its corresponding task.
     */
    public function taskTable()
    {
        return $this->belongsTo(TaskTable::class, 'task_table');
    }

    /**
     * Defines a many-to-many relationship between Task and Account.
     * Retrieves all accounts assigned to this task through the 'account_task' pivot table.
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_task', 'task', 'account');
    }

    /**
     * Defines a one-to-many relationship between Task and ProjectEvent.
     * Retrieves all project events associated with this task.
     */
    public function projectEvents()
    {
        return $this->hasMany(ProjectEvent::class, 'task');
    }

    /**
     * Defines a one-to-many relationship between Task and Comment.
     * Retrieves all comments associated with this task.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'task');
    }

    /**
     * Retrieves the project associated with this task.
     * Navigates through the related task table to fetch the project.
     *
     * @return mixed|null The project associated with the task or null if not found.
     */
    public function getProject()
    {
        return $this->taskTable->getProject()->first();
    }
}