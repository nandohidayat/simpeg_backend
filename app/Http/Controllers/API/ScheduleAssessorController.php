<?php

namespace App\Http\Controllers\API;

use App\ScheduleAssessor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScheduleAssessorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ScheduleAssessor::join('f_department as d', 'd.id_dept', '=', 'schedule_assessors.dept')
            ->join('f_department as a', 'a.id_dept', '=', 'schedule_assessors.assessor')
            ->select('id_schedule_assessor', 'dept', 'assessor', 'd.nm_dept as nm_dept', 'a.nm_dept as nm_ass')
            ->orderBy('nm_dept', 'asc')
            ->orderBy('nm_ass', 'asc')
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
        $data = ScheduleAssessor::create($input);

        if ($data === null) return response()->json(["status" => "failed"], 501);

        $data = ScheduleAssessor::where('id_schedule_assessor', $data->id_schedule_assessor)
            ->join('f_department as d', 'd.id_dept', '=', 'schedule_assessors.dept')
            ->join('f_department as a', 'a.id_dept', '=', 'schedule_assessors.assessor')
            ->select('id_schedule_assessor', 'dept', 'assessor', 'd.nm_dept as nm_dept', 'a.nm_dept as nm_ass')
            ->orderBy('nm_dept', 'asc')
            ->orderBy('nm_ass', 'asc')
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
        $schedule = ScheduleAssessor::find($id);

        if ($schedule === null) return response()->json(["status" => "failed"], 501);

        $schedule->dept = $input['dept'];
        $schedule->assessor = $input['assessor'];
        $schedule->save();

        $data = ScheduleAssessor::where('id_schedule_assessor', $schedule->id_schedule_assessor)
            ->join('f_department as d', 'd.id_dept', '=', 'schedule_assessors.dept')
            ->join('f_department as a', 'a.id_dept', '=', 'schedule_assessors.assessor')
            ->select('id_schedule_assessor', 'dept', 'assessor', 'd.nm_dept as nm_dept', 'a.nm_dept as nm_ass')
            ->orderBy('nm_dept', 'asc')
            ->orderBy('nm_ass', 'asc')
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
        $data = ScheduleAssessor::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 404);

        $data->delete();

        return response()->json(["status" => "success"], 201);
    }
}
