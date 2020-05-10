<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Job;
use App\JobDepartemen;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Job::orderBy('keterangan', 'asc')->get();

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
        $data = Job::create($input);

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
        $input = $request->all();
        $data = Job::find($id);

        if ($data === null) return response()->json(["status" => "failed"], 501);

        $data->color = $input['color'];
        $data->keterangan = $input['keterangan'];
        $data->save();

        return response()->json(["status" => "success"], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Job::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 401);

        $data->delete();
        return response()->json(["status" => "success"], 201);
    }

    public function getDepartemens($id)
    {
        $shift = JobDepartemen::where(['id_dept' => $id, 'status' => true])->pluck('id_job');

        return response()->json(["status" => "success", "data" => $shift], 200);
    }

    public function createDepartemens(Request $request, $id)
    {
        $input = $request->all();
        $job = Job::pluck('id_job');
        $active = array_map('intval', $input['job']);

        foreach ($job as $a) {
            $status = false;
            if (in_array($a, $active, true)) {
                $status = true;
            }
            JobDepartemen::updateOrCreate(['id_job' => $a, 'id_dept' => $id], ['status' => $status]);
        }

        return response()->json(["status" => "success"], 201);
    }
}
