<?php

namespace App\Models;

use App\Http\Controllers\FileController;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Added to define Eloquent relationships.
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Authenticatable implements CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'account';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'workfield',
        'city',               // foreign key for city
        'blocked',
        'admin',
        'account_image_id'     // foreign key for account_image
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'blocked' => 'boolean',
        'admin' => 'boolean',
    ];

    /**
     * Define relationship with the City model.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city');
    }

    /**
     * Define relationship with the AccountImage model.
     */
    public function accountImage()
    {
        return $this->belongsTo(AccountImage::class, 'account_image_id');
    }

    /**
     * Define the many-to-many relationship between Account and Project via ProjectMember.
     */

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_member', 'account', 'project')
                    ->withPivot('is_favourite', 'forum_component', 'analytics_component', 'members_component', 'productivity_component', 'last_accessed');
    }

   /**
     * Defines a many-to-many relationship between Account and Task.
     * This relationship is mediated through the 'account_task' pivot table.
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'account_task', 'account', 'task');
    }

    /**
     * Get all invitations for the account.
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'account');
    }

    /**
     * Defines a one-to-many relationship between Account and Notification.
     * Retrieves all notifications emitted to this account.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'emitted_to');
    }

    /**
     * Defines a one-to-many relationship between Account and ForumMessage.
     * Retrieves all forum messages posted by this account.
     */
    public function forumMessages()
    {
        return $this->hasMany(ForumMessage::class, 'account');
    }

    /**
     * Defines a one-to-many relationship between Account and Comment.
     * Retrieves all comments made by this account.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'account');
    }

    /**
     * Defines a one-to-many relationship between Account and ProjectEvent.
     * Retrieves all project events associated with this account.
     */
    public function projectEvents()
    {
        return $this->hasMany(ProjectEvent::class, 'account');
    }

    /*
     * Get the Account Image File
     */
    public function getAccountImage() {
        return FileController::get('accountAsset', $this->id);
    }


}
