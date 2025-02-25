<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumMessage extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'forum_message';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account',
        'project',
        'content',
        'create_date'
    ];

        /**
     * The attributes that should be cast to specific data types.
     *
     * @var array<string, string>
     */
    protected $casts =[
        'create_date' => 'datetime',
        'content' => 'string',
    ];

    /**
     * Defines a many-to-one relationship between ForumMessage and Account.
     * Associates this forum message with a single account.
     */
    public function getAccount()
    {
        return $this->belongsTo(Account::class, 'account');
    }

    /**
     * Defines a many-to-one relationship between ForumMessage and Project.
     * Associates this forum message with a single project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project');
    }
}
