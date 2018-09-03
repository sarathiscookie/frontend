<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Userlist;
use App\Tempuser;
use Mail;
use PDF;

class CronJobsController extends Controller
{
    /**
     *
     * List temp users
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function tempUser($id)
    {
        $tempUser = Tempuser::select('_id', 'usrFirstname', 'usrLastname', 'usrEmail', 'usrTelephone')
            ->where('is_delete', 0)
            ->find($id);

        if(!empty($tempUser)) {
            return $tempUser;
        }
    }

    /**
     *
     * List users
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function user($id)
    {
        $user = Userlist::select('_id', 'usrFirstname', 'usrLastname', 'usrEmail', 'usrTelephone')
            ->where('is_delete', 0)
            ->find($id);

        if(!empty($user)) {
            return $user;
        }
    }
}
