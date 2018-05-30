<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class PrivateMessage extends Eloquent
{
    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * User object for each message available
     */
    protected $appends = ['sender', 'receiver'];

    public function getSenderAttribute()
    {
        return Userlist::where('_id', $this->sender_id)
            ->where('usrActive', '1')
            ->where('is_delete', 0)
            ->first();
    }

    public function getReceiverAttribute()
    {
        return Userlist::where('_id', $this->receiver_id)
            ->where('usrActive', '1')
            ->where('is_delete', 0)
            ->first();
    }
}
