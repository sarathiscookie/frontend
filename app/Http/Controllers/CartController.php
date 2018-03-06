<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Http\Requests\CartRequest;
use DateTime;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $messages = [
            'dateFrom' => 'Arrival date is required',
            'dateTo'  => 'Departure date is required',
            'persons'  => 'No of persons required',
        ];

        $rules    = [
            'dateFrom'  => 'required',
            'dateTo'  => 'required',
            'persons'  => 'required|not_in:0',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $dateBegin      = DateTime::createFromFormat('d.m.y', $request->dateFrom)->format('Y-m-d');
        $dateEnd        = DateTime::createFromFormat('d.m.y', $request->dateTo)->format('Y-m-d');
        $dateDifference = date_diff(date_create($dateBegin), date_create($dateEnd));
        if($dateBegin >= $dateEnd){
            return response()->json(['status' => 'error', 'message' => 'Arrival date is not same or greater than departure date.']);
        }
        else {
            if($dateDifference->format("%a") <= 60) {
                return response()->json(['status' => 'success'], 200);
            }
            else {
                return response()->json(['status' => 'error', 'message' => 'Maximum 60 days can book']);
            }
        }

        return response()->json(['status' => 'success'], 200);
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
