<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'invitation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project',
        'account',
        'accepted',
    ];

    /**
     * Defines a many-to-one relationship between Invitation and Project.
     * Retrieves the project that this invitation belongs to.
     */
    public function getProject()
    {
        return $this->belongsTo(Project::class, 'project');
    }

    /**
     * Defines a many-to-one relationship between Invitation and Account.
     * Retrieves the account that this invitation is for.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account');
    }
}
