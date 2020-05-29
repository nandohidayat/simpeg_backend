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
        $input = $request->all();
        $thisMonth = Carbon::create($input['year'], $input['month']);
        $firstday = $thisMonth->copy()->firstOfMonth();

        // 0 = initial
        // 1 = created
        // 2 = accepted

        if ((int) $input['status'] === 1) {
            ScheduleRequest::updateOrCreate(['dept' => request()->dept, 'tgl' => $firstday], ['status' => 1, 'requestor' => auth()->user()->id_pegawai]);
        } else if ((int) $input['status'] === 2) {
            ScheduleRequest::updateOrCreate(['dept' => request()->dept, 'tgl' => $firstday], ['status' => 2, 'assessor' => auth()->user()->id_pegawai]);
        } else {
            ScheduleRequest::updateOrCreate(['dept' => request()->dept, 'tgl' => $firstday], ['status' => 0, 'assessor' => null, 'requestor' => null]);
        }

        return response()->json(["status" => "success"], 200);
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
        $thisMonth = Carbon::create($input['year'], $input['month']);
        $firstday = $thisMonth->copy()->firstOfMonth();

        // 0 = initial
        // 1 = created
        // 2 = accepted

        if ((int) $input['status'] === 1) {
            ScheduleRequest::updateOrCreate(['dept' => $id, 'tgl' => $firstday], ['status' => 1, 'requestor' => auth()->user()->id_pegawai]);
        } else if ((int) $input['status'] === 2) {
            ScheduleRequest::updateOrCreate(['dept' => $id, 'tgl' => $firstday], ['status' => 2, 'assessor' => auth()->user()->id_pegawai]);
        } else {
            ScheduleRequest::updateOrCreate(['dept' => $id, 'tgl' => $firstday], ['status' => 0, 'assessor' => null, 'requestor' => null]);
        }

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
