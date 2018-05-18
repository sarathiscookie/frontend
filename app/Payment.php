<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Payment extends Eloquent
{
    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'payment';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['_id'];
}
