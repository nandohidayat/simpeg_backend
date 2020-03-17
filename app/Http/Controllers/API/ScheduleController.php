<?php

namespace App\Http\Controllers\API;

use App\Departemen;
use App\Exports\SchedulesExport;
use App\Http\Controllers\Controller;
use App\Karyawan;
use App\Schedule;
use App\ScheduleAssessor;
use App\ScheduleRequest;
use App\ShiftDepartemen;
use App\SIMDepartment;
use App\SIMLoginPegawai;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
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

        $dept = null;

        if (request()->dept !== null) {
            $dept = request()->dept;
        } else {

            if (strpos(auth()->user()->id_dept, 'd-44') !== false) {
                $query = SIMDepartment::select('id_dept', 'nm_dept');
            } else {
                $query = SIMDepartment::whereRaw('id_dept = ANY(\'' . auth()->user()->id_dept . '\')')
                    ->select('id_dept', 'nm_dept')
                    ->orderBy('nm_dept', 'asc');

                $child = ScheduleAssessor::whereRaw('schedule_assessors.assessor = ANY(\'' . auth()->user()->id_dept . '\')')
                    ->join('f_department', 'f_department.id_dept', '=', 'schedule_assessors.dept')
                    ->select('f_department.id_dept', 'f_department.nm_dept')
                    ->orderBy('f_department.nm_dept', 'asc');

                $query = $query->unionAll($child);
            }

            $listdept = $query->get();
            $dept = $listdept[0]->id_dept;
        }

        $query = DB::table('f_login_pegawai as lp')
            ->whereRaw('\'' . $dept . '\' = ANY(lp.id_dept)')
            ->join('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'lp.id_pegawai')
            ->select('lp.id_pegawai', 'dp.nm_pegawai as nama');

        $jam = '';
        $header = [];
        $weekend = [];

        $obj = new stdClass();
        $obj->text = "Nama";
        $obj->value = "nama";
        $obj->width = "250px";
        array_push($header, $obj);

        $date = $firstday->copy();

        while (!$date->greaterThan($lastday)) {
            $query->leftJoin('schedules as sch' . $date->day . '', function ($q) use ($date, $dept) {
                $q->where('sch' . $date->day . '.dept', '=', $dept);
                $q->on('sch' . $date->day . '.pegawai', '=', 'lp.id_pegawai');
                $q->where('sch' . $date->day . '.tgl', $date->toDateString());
            });
            $query->leftJoin('shifts as shf' . $date->day . '', 'sch' . $date->day . '.shift', '=', 'shf' . $date->day . '.id_shift');
            $query->addSelect('sch' . $date->day . '.shift as day' . $date->day . '');

            $jam .= 'COALESCE(
                CASE
                WHEN shf' . $date->day . '.selesai - shf' . $date->day . '.mulai > time \'00:00\' THEN
                    shf' . $date->day . '.selesai - shf' . $date->day . '.mulai
                ELSE
                    shf' . $date->day . '.selesai - shf' . $date->day . '.mulai + interval \'24 hours\'
                END
                , interval \'0 hours\')';

            if (!$date->equalTo($lastday)) $jam .= ' + ';

            $obj = new stdClass();
            $obj->text = $date->day;
            $obj->value = "day" . ($date->day);
            $obj->sortable = false;
            array_push($header, $obj);

            if ($date->dayOfWeek === 0) array_push($weekend, $date->day);

            $date->addDay();
        }

        $query->addSelect(DB::raw($jam .= 'as jam'));

        $obj = new stdClass();
        $obj->text = "Total Jam";
        $obj->value = "jam";
        $obj->width = "110px";
        array_push($header, $obj);

        $schedules = $query->get();

        $shift = ShiftDepartemen::where(['id_dept' => $dept, 'status' => true])
            ->join('shifts', 'shifts.id_shift', '=', 'shift_departemens.id_shift')
            ->select('shifts.id_shift', 'shifts.kode')
            ->orderBy('mulai', 'asc')
            ->get();

        $karyawan = DB::table('f_login_pegawai as lp')
            ->whereRaw('\'' . $dept . '\' = ANY(lp.id_dept)')
            ->join('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'lp.id_pegawai')
            ->select('lp.id_pegawai', 'dp.nm_pegawai')
            ->get();

        $ass = ScheduleAssessor::whereRaw('schedule_assessors.dept = ANY(\'' . auth()->user()->id_dept . '\')')
            ->orWhereRaw('schedule_assessors.assessor = ANY(\'' . auth()->user()->id_dept . '\') AND schedule_assessors.dept = \'' . $dept . '\'')
            ->first();

        $assessor = null;

        if ($ass !== null) {
            $assessor = new stdClass();

            $assessor->ass = strpos(auth()->user()->id_dept, $ass->assessor);

            $req = ScheduleRequest::firstOrCreate(['assessor' => $ass->id_schedule_assessor, 'tgl' => $firstday], ['status' => 0]);
            $assessor->id = $req->id_schedule_request;
            $assessor->req = $req->status;
        }

        $response = ["schedule" => $schedules, "header" => $header, 'shift' => $shift, 'weekend' => $weekend, 'karyawan' => $karyawan, 'assessor' => $assessor];

        if (request()->dept === null) {
            $response["dept"] = $listdept;
        }

        return response()->json(["status" => "success", "data" => $response], 200);
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

            for ($i = 1; $i <= $last; $i++) {
                $obj = array();
                $obj['dept'] = request()->dept;
                $obj['pegawai'] = $inp['id_pegawai'];
                $obj['tgl'] = Carbon::create(request()->year, request()->month, $i);
                $obj['shift'] = empty($inp['day' . ($i)]) ? null : $inp['day' . $i];
                Schedule::updateOrCreate(
                    ['dept' => $obj['dept'], 'pegawai' => $obj['pegawai'], 'tgl' => $obj['tgl']],
                    ['shift' => $obj['shift']]
                );
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

    public function export($id)
    {
        return Excel::download(new SchedulesExport($id, request()->year, request()->month), 'schedules.xlsx');
    }
}
