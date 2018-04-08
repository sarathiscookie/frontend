<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Booking extends Eloquent
{
    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'booking';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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

    protected $dates = ['bookingdate', 'checkin_from', 'reserve_to', 'cart_expiry_date'];
}
