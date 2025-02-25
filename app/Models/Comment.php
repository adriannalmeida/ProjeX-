<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\EventType;

class Comment extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'comment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account',
        'content',
        'create_date',
        'task'
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'create_date' => 'datetime',
        'content' => 'string',
    ];

    /**
     * Defines a many-to-one relationship between Comment and Account.
     * Associates this comment with a single account.
     */
    public function getAccount()
    {
        return $this->belongsTo(Account::class, 'account');
    }

    /**
     * Defines a many-to-one relationship between Comment and Task.
     * Associates this comment with a single task.
     */
    public function getTask()
    {
        return $this->belongsTo(Task::class, 'task');
    }

    /**
     * Retrieves the project associated with this comment.
     * Navigates through the related task and its task table to fetch the project.
     */
    public function getProject()
    {
        return $this->getTask()->taskTable->getProject()->first();
    }


}
