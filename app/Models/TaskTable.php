<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTable extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'task_table';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'project',
        'position',
    ];

    /**
     * Defines a many-to-one relationship between TaskTable and Project.
     * Retrieves the project that owns this task table using the 'project' foreign key.
     */
    public function getProject()
    {
        return $this->belongsTo(Project::class, 'project');
    }

    /**
     * Defines a one-to-many relationship between TaskTable and Task.
     * Retrieves all tasks associated with this task table.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'task_table');
    }

    /**
     * Defines a one-to-many relationship between TaskTable and Task, ordered by position.
     * Retrieves all tasks associated with this task table, sorted by their 'position' field.
     */
    public function tasksByPosition(){
        return $this->hasMany(Task::class, 'task_table')->orderBy('position');
    }
}
