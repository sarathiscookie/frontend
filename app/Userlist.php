<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Userlist extends Eloquent
{
    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'user';

    /**
     * Indicates if the model should be timestamped. Updating only the usrUpdateDate
     *
     * @var bool
     */
    const UPDATED_AT   = 'usrUpdateDate';
    const CREATED_AT   = 'usrRegistrationDate';
    public $timestamps = [ "UPDATED_AT", "CREATED_AT" ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['_id'];

    /**
     * Eloquent allows you to work with Carbon/DateTime objects instead of MongoDate objects.
     * Internally, these dates will be converted to MongoDate objects when saved to the database.
     */

    protected $dates = ['emailConfirmedDate'];

    /**
     * Set the user's address first letter in to uppercase.
     *
     * @param  string  $value
     * @return void
     */
    public function setusrAddressAttribute($value)
    {
        $this->attributes['usrAddress'] = ucfirst($value);
    }

    /**
     * Set the user's city first letter in to uppercase.
     *
     * @param  string  $value
     * @return void
     */
    public function setusrCityAttribute($value)
    {
        $this->attributes['usrCity'] = ucfirst($value);
    }
}
