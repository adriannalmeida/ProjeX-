<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Defines an enumeration for different layout types associated with project members.
 * These layouts represent positional or configuration preferences for the project member.
 */
enum Layout: string
{
    case None = 'None';
    case RightUp = 'RightUp';
    case RightDown = 'RightDown';
    case LeftUp = 'LeftUp';
    case LeftDown = 'LeftDown';
}

class ProjectMember extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'project_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account', 
        'project', 
        'is_favourite', 
        'forum_component', 
        'analytics_component', 
        'members_component', 
        'productivity_component',
        'last_accessed',
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'forum_component' => Layout::class,
        'analytics_component' => Layout::class,
        'members_component' => Layout::class,
        'productivity_component' => Layout::class,
        'last_accessed' => 'datetime',
    ];

    /**
     * Defines a many-to-one relationship between ProjectMember and Account.
     * Associates this project member with the corresponding account using the 'account' foreign key.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account');
    }

    /**
     * Defines a many-to-one relationship between ProjectMember and Project.
     * Associates this project member with the corresponding project using the 'project' foreign key.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project');
    }
}
