<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Schedule;
use App\ScheduleChange;
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
        $query = DB::table('schedule_changes');
        if (request()->ruang !== null) {
            $query->where('id_ruang', request()->ruang);
        } else {
            $query->where('pemohon', Auth::user()->nik)
                ->orWhere('dengan', Auth::user()->nik);
        }

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
    { }

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
        $data->save();

        if ($data->status === 1) {
            $first = Schedule::where('tgl', $data->tgl)
                ->where('nik', $data->pemohon)
                ->first();

            if ($data->type === 0) {
                $second = Schedule::where('tgl', $data->tgl)
                    ->where('nik', $data->dengan)
                    ->first();

                $temp = $first->id_shift;
                $first->id_shift = $second->id_shift;
                $second->id_shift = $temp;

                $second->save();
            } else if ($data->type === 1) {
                $first->id_shift = $data->id_shift;
            }

            $first->save();
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
        //
    }
}
