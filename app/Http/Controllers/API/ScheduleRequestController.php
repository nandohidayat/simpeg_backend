<?php

namespace App\Http\Controllers\API;

use App\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ScheduleAssessor;

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
        $input = $request->all();
        $data = ScheduleRequest::find($id);

        // 0 = initial
        // 1 = created
        // 2 = accepted

        if ((int) $input['req'] === 1) {
            $data->status = 1;
        } else {
            $data->status = (int) $input['req'];
            $data->pic = auth()->user()->id_pegawai;
        }

        $data->save();

        if ($data === null) return response()->json(["status" => "failed"], 501);
        return response()->json(["status" => "success"], 200);
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
