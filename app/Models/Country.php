<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'country';
    
    /**
     * Defines a one-to-many relationship between Country and City.
     * Retrieves all cities associated with this country.
     */
    public function cities(){
        return $this->hasMany(City::class, 'country');
    }
}
