<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Schedule;
use App\ScheduleChange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleChangeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date = Carbon::create(request()->year, request()->month);
        $firstday = $date->copy()->firstOfMonth();
        $lastday = $date->copy()->lastOfMonth()->addDay();

        $query = ScheduleChange::where([['dept', '=', request()->dept], ['created_at', '>=', $firstday], ['created_at', '<', $lastday]])->orderBy('created_at', 'desc');

        $data = $query->get();

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

        $data = ScheduleChange::create($input);

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
        $data = ScheduleChange::find($id);

        $data->status = $request->status;
        $data->kepala = auth()->user()->id_pegawai;

        if ((int) $data->type === 2 && (int) $request->status === 2 && $request->dengan !== null) {
            $data->dengan = $request->dengan;
        }

        $data->save();

        if ((int) $request->status === 3)
            return response()->json(["status" => "success"], 201);

        $first = Carbon::create($data->mulai);
        $last = Carbon::create($data->selesai);

        while ($first->lessThanOrEqualTo($last)) {
            if ((int) $data->type === 1) {
                $pemohon = Schedule::where([
                    ['pegawai', '=', $data->pemohon],
                    ['tgl', '=', $first]
                ])->first();
                $dengan = Schedule::where([
                    ['pegawai', '=', $data->dengan],
                    ['tgl', '=', $first]
                ])->first();

                Schedule::where('id_schedule', $pemohon['id_schedule'])->update(['shift' => $dengan['shift']]);
                Schedule::where('id_schedule', $dengan['id_schedule'])->update(['shift' => $pemohon['shift']]);
            } else if ((int) $data->type === 2) {

                $pemohon = Schedule::where([
                    ['pegawai', '=', $data->pemohon],
                    ['tgl', '=', $first]
                ])->first();

                if ($request->dengan !== null) {
                    $dengan = Schedule::where([
                        ['pegawai', '=', $request->dengan],
                        ['tgl', '=', $first]
                    ])->first();

                    Schedule::where('id_schedule', $dengan['id_schedule'])->update(['shift' => $pemohon['shift']]);
                }

                if ($pemohon === null) {
                    Schedule::create(['tgl' => $first, 'pegawai' => $data->pemohon, 'shift' => 4]);
                } else {
                    Schedule::where('id_schedule', $pemohon['id_schedule'])->update(['shift' => 4]);
                }
            }

            $first->addDay();
        }

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
        $data = ScheduleChange::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 404);

        $data->delete();
        return response()->json(["status" => "success"], 201);
    }
}
