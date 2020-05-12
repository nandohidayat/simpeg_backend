<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ScheduleAccess;

class ScheduleAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ScheduleAccess::join('f_department as d', 'd.id_dept', '=', 'schedule_accesses.dept')
            ->join('f_department as a', 'a.id_dept', '=', 'schedule_accesses.access')
            ->select('id_schedule_access', 'dept', 'access', 'assessor', 'd.nm_dept as nm_dept', 'a.nm_dept as nm_acc')
            ->orderBy('nm_dept', 'asc')
            ->orderBy('nm_acc', 'asc')
            ->get();

        if ($data === null) return response()->json(["status" => "failed"], 501);
        return response()->json(["status" => "success", "data" => $data], 200);
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
        $data = ScheduleAccess::create($input);

        if ($data === null) return response()->json(["status" => "failed"], 501);

        $data = ScheduleAccess::where('id_schedule_access', $data->id_schedule_access)
            ->join('f_department as d', 'd.id_dept', '=', 'schedule_accesses.dept')
            ->join('f_department as a', 'a.id_dept', '=', 'schedule_accesses.access')
            ->select('id_schedule_access', 'dept', 'access', 'assessor', 'd.nm_dept as nm_dept', 'a.nm_dept as nm_acc')
            ->orderBy('nm_dept', 'asc')
            ->orderBy('nm_acc', 'asc')
            ->first();

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
        $input = $request->all();
        $schedule = ScheduleAccess::find($id);

        if ($schedule === null) return response()->json(["status" => "failed"], 501);

        $schedule->dept = $input['dept'];
        $schedule->access = $input['access'];
        $schedule->assessor = $input['assessor'];
        $schedule->save();

        $data = ScheduleAccess::where('id_schedule_access', $schedule->id_schedule_access)
            ->join('f_department as d', 'd.id_dept', '=', 'schedule_accesses.dept')
            ->join('f_department as a', 'a.id_dept', '=', 'schedule_accesses.access')
            ->select('id_schedule_access', 'dept', 'access', 'assessor', 'd.nm_dept as nm_dept', 'a.nm_dept as nm_acc')
            ->orderBy('nm_dept', 'asc')
            ->orderBy('nm_acc', 'asc')
            ->first();

        return response()->json(["status" => "success", "data" => $data], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = ScheduleAccess::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 404);

        $data->delete();

        return response()->json(["status" => "success"], 201);
    }
}
