<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Season extends Eloquent
{
    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'seasons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Eloquent allows you to work with Carbon/DateTime objects instead of MongoDate objects.
     * Internally, these dates will be converted to MongoDate objects when saved to the database.
     */
    protected $dates = ['earliest_summer_open', 'earliest_summer_close', 'latest_summer_open', 'latest_summer_close', 'summer_next_season', 'earliest_winter_open', 'earliest_winter_close', 'latest_winter_open', 'latest_winter_close', 'winter_next_season'];
}
