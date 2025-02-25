<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'city';

    /**
     * Defines a many-to-one relationship between City and Country.
     * Associates this city with a single country.
     */
    public function country(){
        return $this->belongsTo(Country::class, 'country');
    }
    /**
     * Defines a one-to-one relationship between City and Account.
     * Associates this city with a single account.
     */
    public function account(){
        return $this->hasOne(City::class, 'city');
    }
}
