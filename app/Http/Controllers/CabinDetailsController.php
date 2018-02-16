<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cabin;

class CabinDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $cabin_id = preg_replace(sprintf('/%s/', env('MD5_Key')), '', base64_decode($id));

        $cabin = Cabin::where('is_delete', 0)
            /*->where('other_cabin', "0")*/
            ->first($cabin_id);

        dd($cabin);

        return view('cabinDetails');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
