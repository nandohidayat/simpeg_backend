<?php

namespace App\Http\Controllers\API;

use App\Exports\SchedulesExport;
use App\Http\Controllers\Controller;
use App\Imports\SchedulesImport;
use App\JobDepartemen;
use App\Karyawan;
use App\Schedule;
use App\ScheduleAccess;
use App\ScheduleAssessor;
use App\ScheduleHoliday;
use App\ScheduleOrder;
use App\ScheduleRequest;
use App\ShiftDepartemen;
use App\SIMDepartment;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class ScheduleController extends Controller
{
    function timeAdd($first, $second)
    {
        $time1 = explode(':', $first);
        $time2 = explode(':', $second);

        $second = (int) $time1[2] + $time2[2];
        $temp = (int) $second / 60;
        $second = $second % 60;

        $minute = (int) $time1[1] + $time2[1] + $temp;
        $temp = (int) $minute / 60;
        $minute = $minute % 60;

        $hour = (int) $time1[0] + $time2[0] + $temp;

        return '' . sprintf("%02d", $hour) . ':' . sprintf("%02d", $minute) . ':' . sprintf("%02d", $second) . '';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $thisMonth = null;
        if (request()->year || request()->month) {
            $thisMonth = Carbon::create(request()->year, request()->month);
        } else {
            $thisMonth = Carbon::now(new DateTimeZone('Asia/Jakarta'));
        }

        $firstday = $thisMonth->copy()->firstOfMonth();
        $lastday = $thisMonth->copy()->lastOfMonth();
        $today = Carbon::now(new DateTimeZone('Asia/Jakarta'))->toDateString();

        $dept = null;

        if (request()->dept !== null) {
            $dept = request()->dept;
            $order = explode(',', SIMDepartment::where('f_department.id_dept', $dept)
                ->leftJoin('schedule_orders as so', 'so.id_dept', '=', 'f_department.id_dept')
                ->select('so.order')
                ->first()
                ->order);
        } else {
            $semua = DB::table('users as us')
                ->join('akses_groups as ag', 'ag.id_group', '=', 'us.id_group')
                ->where('us.id_pegawai', auth()->user()->id_pegawai)
                ->where('ag.id_akses', 6)
                ->where('ag.status', true)
                ->first();

            if ($semua) {
                $data = DB::table('f_department')
                    ->leftJoin('schedule_orders as so', 'so.id_dept', '=', 'f_department.id_dept')
                    ->select('f_department.id_dept', 'f_department.nm_dept', 'so.order')
                    ->orderBy('nm_dept', 'asc')
                    ->get();
            } else {
                $query = DB::table('log_departemens as ld')
                    ->where('ld.id_pegawai', auth()->user()->id_pegawai)
                    ->whereRaw('ld.masuk <= \'' . $today . '\'')
                    ->whereRaw('coalesce(ld.keluar, \'' . $today . '\') >= \'' . $today . '\'')
                    ->join('f_department as fd', 'fd.id_dept', '=', 'ld.id_dept')
                    ->leftJoin('schedule_orders as so', 'so.id_dept', '=', 'fd.id_dept')
                    ->select('fd.id_dept', 'fd.nm_dept', 'so.order');

                $child = DB::table('log_departemens as ld')
                    ->where('ld.id_pegawai', auth()->user()->id_pegawai)
                    ->whereRaw('ld.masuk <= \'' . $today . '\'')
                    ->whereRaw('coalesce(ld.keluar, \'' . $today . '\') >= \'' . $today . '\'')
                    ->rightJoin('schedule_accesses as sa', 'sa.access', '=', 'ld.id_dept')
                    ->join('f_department as fd', 'fd.id_dept', '=', 'sa.dept')
                    ->leftJoin('schedule_orders as so', 'so.id_dept', '=', 'fd.id_dept')
                    ->select('fd.id_dept', 'fd.nm_dept', 'so.order');

                $data = $query->union($child)->orderBy('nm_dept')->get();
            }

            $depts = $data;
            $dept = $depts[0]->id_dept;
            $order = explode(',', $depts[0]->order);
        }

        $query = DB::table('log_departemens as ld')
            ->where('ld.id_dept', $dept)
            ->where('ld.masuk', '<=', $lastday)
            ->whereRaw('coalesce(ld.keluar, \'' . $lastday . '\') >= \'' . $firstday . '\'')
            ->join('f_data_pegawai as dp', function ($query) {
                $query->on('dp.id_pegawai', '=', 'ld.id_pegawai');
                $query->where('dp.is_active', true);
            })
            ->select('dp.id_pegawai', 'dp.nm_pegawai as nama')
            ->orderBy('nik_pegawai');

        $weekend = [];

        $date = $firstday->copy();

        while (!$date->greaterThan($lastday)) {
            $query->leftJoin('schedules as sch' . $date->day . '', function ($q) use ($date, $dept) {
                $q->where('sch' . $date->day . '.dept', '=', $dept);
                $q->on('sch' . $date->day . '.pegawai', '=', 'dp.id_pegawai');
                $q->where('sch' . $date->day . '.tgl', $date->toDateString());
            });

            $query->leftJoin('shifts as shf' . $date->day . '', 'shf' . $date->day . '.id_shift', '=', 'sch' . $date->day . '.shift');

            $query->addSelect('sch' . $date->day . '.shift as shift' . $date->day . '');
            $query->selectRaw('COALESCE(
                CASE
                WHEN shf' . $date->day . '.selesai - shf' . $date->day . '.mulai >= time \'00:00\' THEN
                    shf' . $date->day . '.selesai - shf' . $date->day . '.mulai
                ELSE
                    shf' . $date->day . '.selesai - shf' . $date->day . '.mulai + interval \'24 hours\'
                END
                , interval \'0 hours\') as jam' . $date->day);

            if ($date->dayOfWeek === 0) array_push($weekend, $date->day);

            $date->addDay();
        }

        $schedules = $query->get();

        $ass = ScheduleAccess::whereRaw('(schedule_accesses.dept = \'' . $dept . '\' OR (schedule_accesses.access = \'' . $dept . '\' AND \'' . $dept . '\' != ANY(\'' . auth()->user()->id_dept . '\'))) AND schedule_accesses.assessor = true')
            ->leftJoin('schedule_requests as sr', function ($q) use ($firstday) {
                $q->on('sr.dept', '=', 'schedule_accesses.dept');
                $q->where('tgl', $firstday);
            })
            ->select(DB::raw('(CASE WHEN schedule_accesses.access = ANY(\'' . auth()->user()->id_dept . '\') THEN true ELSE false END) as assessor'), 'sr.status')
            ->first();

        $holiday = ScheduleHoliday::whereBetween('tgl', [$firstday, $lastday])->select(DB::raw('EXTRACT(DAY FROM tgl) as tgl'))->pluck('tgl');

        $id = [];
        $nama = [];
        $shift = [];
        $jam = [];
        foreach ($schedules as $s) {
            array_push($id, $s->id_pegawai);
            array_push($nama, $s->nama);
            $stemp = [];
            $temp = '00:00:00';
            for ($i = 1; $i <= $lastday->day; $i++) {
                array_push($stemp, $s->{'shift' . $i});
                $temp = $this->timeAdd($temp, $s->{'jam' . $i});
            }
            array_push($shift, $stemp);
            array_push($jam, $temp);
        }

        $count = count($id);

        if ($count > 0) {
            if ($order[0] == "") {
                $order = array(0);
            }

            $max = max(array_map('intval', $order));

            if ($max < $count - 1) {
                for ($i = $max + 1; $i < $count; $i++) {
                    array_push($order, $i);
                }
            } else if ($max > $count - 1) {
                while ($max !== $count - 1) {
                    unset($order[array_search($max, $order)]);
                    $max = max(array_map('intval', $order));
                }
            }

            while ($order[0] === 'NaN') {
                array_shift($order);
            }

            while (end($order) === 'NaN') {
                array_pop($order);
            }
        } else {
            $order = array();
        }

        $order = array_values($order);

        $response = ["order" => $order, "id" => $id, "nama" => $nama, 'day' => $lastday->day, 'shift' => $shift, 'jam' => $jam, 'weekend' => $weekend, 'holiday' => $holiday];

        if ($ass !== null) {
            $response["assessor"] = $ass;
        }

        if (request()->dept === null) {
            $response["dept"] = $depts;
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
        $input = $request->all();

        $query = 'INSERT INTO schedules (dept, pegawai, tgl, shift) VALUES ';

        for ($i = 0; $i < count($input['id']); $i++) {
            for ($j = 0; $j < count($input['shift'][0]); $j++) {
                $query .= '(\'' . $id . '\', \'' . $input['id'][$i] . '\', \'' . Carbon::create($input['year'], $input['month'], $j + 1) . '\',' . (empty($input['shift'][$i][$j]) ? 'null' :  $input['shift'][$i][$j]) . ')';

                if ($j !== count($input['shift'][0]) - 1) {
                    $query .= ', ';
                }
            }
            if ($i !== count($input['id']) - 1) {
                $query .= ', ';
            }
        }

        $query .= ' ON CONFLICT ON CONSTRAINT schedules_ukey DO UPDATE SET shift = excluded.shift';

        DB::select($query);

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

    public function holiday()
    {
        $endpoint = "https://raw.githubusercontent.com/guangrei/Json-Indonesia-holidays/master/calendar.json";
        $client = new \GuzzleHttp\Client();

        $data = $client->request('GET', $endpoint)->getBody();
        $data = json_decode($data);

        foreach ($data as $key => $value) {
            if ($key === 'created-at' || strpos($value->deskripsi, 'Cuti Bersama') !== false) continue;

            $year = substr($key, 0, 4);
            $month = substr($key, 4, 2);
            $day = substr($key, 6, 2);

            $date = Carbon::create($year, $month, $day);

            ScheduleHoliday::updateOrCreate(['tgl' => $date, 'keterangan' => $value->deskripsi]);
        }

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    public function print()
    {
        $thisMonth = Carbon::create(request()->year, request()->month);
        $firstday = $thisMonth->copy()->firstOfMonth();
        $lastday = $thisMonth->copy()->lastOfMonth();

        $dept = request()->dept;

        $query = DB::table('f_data_pegawai as dp')
            ->whereRaw('\'' . $dept . '\' = ANY(dp.id_dept)')
            ->where('is_active', true)
            ->select('dp.id_pegawai', 'dp.nm_pegawai as nama')
            ->orderBy('nik_pegawai');

        $weekend = [];

        $date = $firstday->copy();

        while (!$date->greaterThan($lastday)) {
            $query->leftJoin('schedules as sch' . $date->day . '', function ($q) use ($date, $dept) {
                $q->where('sch' . $date->day . '.dept', '=', $dept);
                $q->on('sch' . $date->day . '.pegawai', '=', 'dp.id_pegawai');
                $q->where('sch' . $date->day . '.tgl', $date->toDateString());
            });

            $query->leftJoin('shifts as shf' . $date->day . '', 'shf' . $date->day . '.id_shift', '=', 'sch' . $date->day . '.shift');

            $query->addSelect('shf' . $date->day . '.kode as shift' . $date->day . '');
            $query->addSelect('sch' . $date->day . '.job as job' . $date->day . '');

            if ($date->dayOfWeek === 0) array_push($weekend, $date->day);

            $date->addDay();
        }

        $schedules = $query->get();

        $dept = SIMDepartment::where('id_dept', $dept)->select('nm_dept')->first()->nm_dept;
        $holiday = ScheduleHoliday::whereBetween('tgl', [$firstday, $lastday])->select(DB::raw('EXTRACT(DAY FROM tgl) as tgl'))->pluck('tgl');

        $nama = [];
        $shift = [];
        $job = [];
        foreach ($schedules as $s) {
            array_push($nama, $s->nama);
            $stemp = [];
            $jtemp = [];
            for ($i = 1; $i <= $lastday->day; $i++) {
                array_push($stemp, $s->{'shift' . $i});
                array_push($jtemp, $s->{'job' . $i});
            }
            array_push($shift, $stemp);
            array_push($job, $jtemp);
        }

        $response = ["dept" => $dept, "nama" => $nama, 'day' => $lastday->day, 'shift' => $shift, 'job' => $job, 'weekend' => $weekend, 'holiday' => $holiday];

        return response()->json(["status" => "success", "data" => $response], 200);
    }

    public function export()
    {
        $dept = SIMDepartment::where('id_dept', request()->dept)->first();
        return Excel::download(new SchedulesExport(request()->dept, request()->year, request()->month), '' . str_replace('/', ' ', $dept->nm_dept) . '(' . request()->year . '-' . request()->month . ').xlsx');
    }

    public function import()
    {
        Excel::import(new SchedulesImport(request()->year, request()->month), request()->file('schedules'));

        return response()->json(["status" => "success"], 200);
    }
}
