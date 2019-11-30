<?php


namespace App\Http\Controllers\API;

use App\Departemen;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Karyawan;
use App\Penilaian;
use App\Rekan;
use App\Ruang;
use App\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use Validator;

use function GuzzleHttp\Promise\all;

class ScheduleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tahun, $bulan)
    {
        $firstday = Carbon::create($tahun, $bulan)->firstOfMonth();
        $lastday = Carbon::create($tahun, $bulan)->lastOfMonth();

        $id_ruang = Karyawan::where('nik', Auth::user()->nik)->first()->id_ruang;

        $schedules = Karyawan::with(['schedules' => function ($query) use ($firstday, $lastday) {
            $query->whereBetween('tgl', [$firstday, $lastday]);
        }])
            ->where('id_ruang', $id_ruang)
            ->orderBy('nik', 'asc')->get();

        $data = [];
        $last = Carbon::create($tahun, $bulan)->lastOfMonth()->day;

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

        $ruang = Ruang::where('id_ruang', $id_ruang)->first()->ruang;

        return response()->json(["status" => "success", "data" => ["schedule" => $data, "ruang" => $ruang]], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $tahun, $bulan)
    {
        $input = $request->all();
        $last = Carbon::create($tahun, $bulan)->lastOfMonth()->day;

        foreach ($input as $inp) {
            for ($i = 0; $i < $last; $i++) {
                $obj = array();
                $obj['nik'] = $inp['nik'];
                $obj['tgl'] = Carbon::create($tahun, $bulan, $i + 1);
                $obj['id_shift'] = empty($inp['day' . ($i + 1)]) ? null : $inp['day' . ($i + 1)];
                Schedule::updateOrCreate(['nik' => $obj['nik'], 'tgl' => $obj['tgl']], $obj);
            }
        }

        return $this->sendResponse([], 'Sukses mang, yeyeyeyeye');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Karyawan::where('nik', $id)->with('departemen', 'ruang')->first();
        return $this->sendResponse($data, 'Sukses mang, yeyeyeyeye');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        Karyawan::updateOrCreate(['nik' => $request->nik], $request->all());
        return $this->sendResponse([], 'Sukses mang, yeyeyeyeye');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();
        return $this->sendResponse([], 'Sukses mang, yeyeyeyeye');
    }

    public function getSchedules($tahun, $bulan)
    { }
}