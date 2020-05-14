<?php

namespace App\Http\Controllers\API;

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
        $parent = ShiftDepartemen::where(['id_dept' => $id, 'status' => true])
            ->join('shifts', function ($q) {
                $q->on('shifts.id_shift', '=', 'shift_departemens.id_shift');
                $q->where('shifts.mulai', '=', '00:00:00');
                $q->where('shifts.selesai', '=', '00:00:00');
            })
            ->orderBy('mulai', 'asc')
            ->select('shift_departemens.id_shift');

        $data = ShiftDepartemen::where(['id_dept' => $id, 'status' => true])
            ->join('shifts', function ($q) {
                $q->on('shifts.id_shift', '=', 'shift_departemens.id_shift');
                $q->where('shifts.mulai', '!=', '00:00:00');
                $q->where('shifts.selesai', '!=', '00:00:00');
            })
            ->orderBy('mulai', 'asc')
            ->unionAll($parent)
            ->pluck('shift_departemens.id_shift');

        return response()->json(["status" => "success", "data" => $data], 200);
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
        $data = Shift::find($id);

        if ($data === null) return response()->json(["status" => "failed"], 501);

        $data->mulai = $input['mulai'];
        $data->selesai = $input['selesai'];
        $data->kode = $input['kode'];
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
        $data = Shift::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 401);

        $data->delete();
        return response()->json(["status" => "success"], 201);
    }

    public function getDepartemens($id)
    {
        $shift = ShiftDepartemen::where(['id_dept' => $id, 'status' => true])->pluck('id_shift');

        return response()->json(["status" => "success", "data" => $shift], 200);
    }

    public function updateDepartemens(Request $request, $id)
    {

        $input = $request->all();
        error_log(json_encode($input));
        $shift = Shift::pluck('id_shift');
        $active = array_map('intval', $input['shift']);

        foreach ($shift as $a) {
            $status = false;
            if (in_array($a, $active, true)) {
                $status = true;
            }
            ShiftDepartemen::updateOrCreate(['id_shift' => $a, 'id_dept' => $id], ['status' => $status]);
        }

        return response()->json(["status" => "success"], 201);
    }
}
