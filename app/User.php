<?php

namespace App;

use Illuminate\Notifications\Notifiable;
/*use Illuminate\Foundation\Auth\User as Authenticatable;*/
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'user';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

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
     * Set the user's first name.
     *
     * @param  string  $value
     * @return void
     */
    public function setusrFirstnameAttribute($value)
    {
        $this->attributes['usrFirstname'] = ucfirst($value);
    }

    /**
     * Set the user's last name.
     *
     * @param  string  $value
     * @return void
     */
    public function setusrLastnameAttribute($value)
    {
        $this->attributes['usrLastname'] = ucfirst($value);
    }

    /**
     * Set the user's email.
     *
     * @param  string  $value
     * @return void
     */
    public function setusrEmailAttribute($value)
    {
        $this->attributes['usrEmail'] = strtolower($value);
    }

    /**
     * Eloquent allows you to work with Carbon/DateTime objects instead of MongoDate objects.
     * Internally, these dates will be converted to MongoDate objects when saved to the database.
     */

    protected $dates = ['emailConfirmedDate', 'lastlogin'];
}
