<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTask extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'account_task';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account',
        'task',
    ];

    /**
     * Defines a many-to-one relationship between AccountTask and Account.
     * Associates this pivot model with a single account using the 'account' foreign key.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account');
    }

    /**
     * Defines a many-to-one relationship between AccountTask and Task.
     * Associates this pivot model with a single task using the 'task' foreign key.
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task');
    }
}
