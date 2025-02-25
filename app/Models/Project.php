<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'project';

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'isPublic',
        'archived',
        'createDate',
        'finishDate',
        'project_coordinator_id',
        'add_deadline_permission',
        'create_task_permission',
        'edit_task_permission',
        'create_tasktable_permission',
        'add_member_permission',
        'view_deleted_tasks_permission',
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'isPublic' => 'boolean',
        'createDate' => 'date',
        'finishDate' => 'date',
    ];

    /**
     * Defines a many-to-one relationship between Project and Account.
     * Associates this project with its coordinator using the 'project_coordinator_id' foreign key.
     */
    public function coordinator(){
        return $this->belongsTo(Account::class, 'project_coordinator_id');
    }

    /**
     * Define the many-to-many relationship between Project and Account via ProjectMember.
    */
    public function members()
    {
        return $this->belongsToMany(Account::class, 'project_member', 'project', 'account')
                    ->withPivot('is_favourite', 'forum_component', 'analytics_component', 'members_component', 'productivity_component');
    }

    /**
     * Get all invitations for the project.
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'project');
    }


    /**
     * Get the task tables for the project.
     *
     */
    public function taskTables()
    {
        return $this->hasMany(TaskTable::class, 'project');
    }

    /**
     * Defines a one-to-many relationship between Project and ForumMessage.
     * Retrieves all forum messages associated with this project.
     */
    public function forumMessages()
    {
        return $this->hasMany(ForumMessage::class, 'project'); 
    }

    /**
     * Defines a one-to-many relationship between Project and Notification.
     * Retrieves all notifications associated with this project.
     */
    public function notifications()
    {
        return $this->hasMany(
            Notification::class,
            'project',
            'id',
        );
    }

    /**
     * Retrieves all project events associated with this project.
     * Filters project events by tasks belonging to task tables that are part of this project.
     */
    public function projectEvents()
    {
        return ProjectEvent::whereHas('getTask', function ($query) {
            $query->whereHas('taskTable', function ($subQuery) {
                $subQuery->where('project', $this->id);
            });
        });
    }
}

