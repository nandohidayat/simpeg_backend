<?php

namespace App\Http\Controllers\API;

use App\Departemen;
use App\Http\Controllers\Controller;
use App\Karyawan;
use App\Schedule;
use App\ShiftDepartemen;
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
        $firstday = $thisMonth->copy()->firstOfMonth();
        $lastday = $thisMonth->copy()->lastOfMonth();

        $nik = request()->nik ? request()->nik : Auth::user()->nik;

        $karyawan = Karyawan::where('nik', $nik)
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
            $obj = new stdClass();

            $obj->nik = $s->nik;
            $obj->nama = $s->nama;
            $obj->shift = ShiftDepartemen::where('id_departemen', $s->id_departemen)->pluck('id_shift');

            for ($i = 0; $i < $last; $i++) {
                $obj->{'day' . ($i + 1)} = empty($s->schedules[$i]) ? null : $s->schedules[$i]->id_shift;
            }

            array_push($data, $obj);
        }

        $ruang = $karyawan->ruang;

        $header = [];

        $obj = new stdClass();
        $obj->text = "Nama";
        $obj->value = "nama";
        $obj->width = "250px";
        array_push($header, $obj);

        for ($i = 0; $i < $last; $i++) {
            $obj = new stdClass();
            $obj->text = "" . ($i + 1) . "";
            $obj->value = "day" . ($i + 1);
            $obj->sortable = false;
            array_push($header, $obj);
        }

        return response()->json(["status" => "success", "data" => ["schedule" => $data, "header" => $header, "ruang" => $ruang]], 200);
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
        $last = Carbon::create(request()->year, request()->month)->lastOfMonth()->day;

        foreach ($input as $inp) {
            if (!is_array($inp)) break;

            for ($i = 0; $i < $last; $i++) {
                $obj = array();
                $obj['nik'] = $inp['nik'];
                $obj['tgl'] = Carbon::create(request()->year, request()->month, $i + 1);
                $obj['id_shift'] = empty($inp['day' . ($i + 1)]) ? null : $inp['day' . ($i + 1)];
                $aaa = Schedule::updateOrCreate(['nik' => $obj['nik'], 'tgl' => $obj['tgl']], $obj);
            }
        }

        return response()->json(["status" => "success"], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $thisMonth = Carbon::create(request()->year, request()->month);
        $firstday = $thisMonth->copy()->firstOfMonth();
        $lastday = $thisMonth->copy()->lastOfMonth();

        $schedules = Karyawan::with(['schedules' => function ($query) use ($firstday, $lastday) {
            $query->whereBetween('tgl', [$firstday, $lastday]);
        }])
            ->where('nik', $id)
            ->first();

        $last = $lastday->day;

        $data = new stdClass();
        $data->nik = $schedules->nik;
        $data->nama = $schedules->nama;

        for ($i = 0; $i < $last; $i++) {
            $data->{'day' . ($i + 1)} = empty($schedules->schedules[$i]) ? null : $schedules->schedules[$i]->id_shift;
        }

        return response()->json(["status" => "success", "data" => array($data)], 200);
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
