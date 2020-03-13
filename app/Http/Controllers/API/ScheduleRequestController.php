<?php

namespace App\Http\Controllers\API;

use App\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScheduleRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        // Ayyub 1 = d-5
        // Ayyub 2 = d-6
        // Ayyub 3 = d-7
        // Ismail 2 = d-63
        // Sulaiman 3 = d-65
        // Sulaiman 4 = d-74
        // Sulaiman 5 = d-10
        // Sulaiman 6 = d-66
        // Sulaiman 7 = d-75

        $perawat = ['d-5', 'd-6', 'd-7', 'd-63', 'd-65', 'd-74', 'd-10', 'd-66', 'd-75'];

        $input['tgl'] = Carbon::create($input['year'], $input['month']);
        $input['from'] = $input['dept'];

        if (in_array($input['dept'], $perawat)) {
            $input['to'] = 'd-14';
        }

        $data = ScheduleRequest::create($input);

        if ($data === null) return response()->json(["status" => "failed"], 501);
        return response()->json(["status" => "success", "data" => $data], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
