<?php

namespace App\Http\Controllers\API;

use App\Departemen;
use App\Http\Controllers\Controller;
use App\Shift;
use App\ShiftDepartemen;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Shift::orderBy('mulai', 'asc')->get();

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
        $data = Shift::create($input);

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
        $data = Shift::updateOrCreate(['id_shift' => $id], $request->all());

        if ($data === null) return response()->json(["status" => "failed"], 501);
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
        $data = Shift::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 401);

        $data->delete();
        return response()->json(["status" => "success"], 201);
    }

    public function getDepartemens($id)
    {
        $departemen = Departemen::with('shiftDepartemens')->where('id_departemen', $id)->first();
        $data = [];

        foreach ($departemen->shiftDepartemens as $d) {
            array_push($data, $d->id_shift);
        }

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    public function createDepartemens(Request $request)
    {
        $input = $request->all();
        $shift = Shift::all();

        foreach ($shift as $a) {
            $status = false;
            if (in_array($a->id_shift, $input['shift'], true)) {
                $status = true;
            }
            ShiftDepartemen::updateOrCreate(['id_shift' => $a->id_shift, 'id_departemen' => $input['departemen']], ['status' => $status]);
        }

        return response()->json(["status" => "success"], 201);
    }
}
