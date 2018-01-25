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
}
