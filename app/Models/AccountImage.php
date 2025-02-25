<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountImage extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'account_image';

    /**
     * Defines a one-to-one relationship between AccountImage and Account.
     */
    public function account(){
        return $this->hasOne(Account::class, 'account_image_id');
    }
}
