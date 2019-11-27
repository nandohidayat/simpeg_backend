<?php

namespace App\Http\Controllers\API;

use App\Departemen;
use App\Http\Controllers\Controller;
use App\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use stdClass;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $thisMonth = Carbon::create(request()->year, request()->month);
        $firstday = $thisMonth->firstOfMonth();
        $lastday = $thisMonth->lastOfMonth();

        $karyawan = Karyawan::where('nik', Auth::user()->nik)
            ->join('ruangs', 'karyawans.id_ruang', '=', 'ruangs.id_ruang')
            ->select('ruangs.id_ruang', 'ruangs.ruang')
            ->first();

        $schedules = Karyawan::with(['schedules' => function ($query) use ($firstday, $lastday) {
            $query->whereBetween('tgl', [$firstday, $lastday]);
        }])
            ->where('id_ruang', $karyawan->id_ruang)
            ->orderBy('nik', 'asc')->get();

        $data = [];
        $last = $lastday->day;

        foreach ($schedules as $s) {
            $departemen = Departemen::with('shiftDepartemens')->where('id_departemen', $s->id_departemen)->first();

            $obj = new stdClass();
            $obj->nik = $s->nik;
            $obj->nama = $s->nama;

            for ($i = 0; $i < $last; $i++) {
                $obj->{'day' . ($i + 1)} = empty($s->schedules[$i]) ? null : $s->schedules[$i]->id_shift;
            }

            $obj->shift = [];
            foreach ($departemen->shiftDepartemens as $s) {
                array_push($obj->shift, $s->id_shift);
            }
            array_push($data, $obj);
        }

        $ruang = $karyawan->ruang;

        return response()->json(["status" => "success", "data" => ["schedule" => $data, "ruang" => $ruang]], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
