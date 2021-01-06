<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Karyawan;
use App\SIMDataPegawai;
use App\SIMLoginPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use Tymon\JWTAuth\Facades\JWTAuth;

class KaryawanController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = DB::table('f_data_pegawai as fdp');
        if ((int)request()->select === 1) {
            if (request()->for === 'ant') {
                if ((int)request()->user === 1) {
                    $query->whereNull('flp.user_pegawai');
                }
                $query->leftJoin('f_login_pegawai as flp', 'flp.id_pegawai', '=', 'fdp.id_pegawai');
                $query->select('fdp.id_pegawai as value', 'fdp.nm_pegawai as label');
            } else {
                if (request()->dept) {
                    $query->whereRaw('\'' . request()->dept . '\' = ANY(fdp.id_dept)');
                }
                $query->select('fdp.id_pegawai', 'fdp.nm_pegawai');
                $query->orderBy('nik_pegawai');
            }
        } else {
            $query->leftJoin('f_department as fd', 'fd.id_dept', '=', DB::raw('ANY(fdp.id_dept)'));
            $query->select('fdp.id_pegawai', 'fdp.nik_pegawai as nik', 'fdp.nm_pegawai as nama', 'fdp.jenis_kelamin as kelamin', DB::raw('json_agg(fd.nm_dept) as dept'), DB::raw('case when fdp.is_active = true then \'Active\' else \'Non Active\' end as status'));
            $query->groupBy('fdp.id_pegawai', 'fdp.nik_pegawai', 'fdp.nm_pegawai', 'fdp.jenis_kelamin', 'fdp.is_active');
            $query->orderBy('nik');
        }

        $data = $query->get();

        if ((int)request()->select !== 1) {
            $data = $data->map(function ($d) {
                $d->dept = json_decode($d->dept)[0] !== null ? json_decode($d->dept) : [];
                return $d;
            });
        }

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

        $dup = DB::table('f_data_pegawai')->where('nik_pegawai', sprintf("%05d", (int) $input['nik']))->first();

        if ($dup) {
            return response()->json(["status" => "error", 'message' => 'Duplicated NIK Pegawai'], 501);
        }

        $id = DB::table('fi_id_pegawai_seq')->first()->id_pegawai;
        DB::table('f_data_pegawai')
            ->insert(
                [
                    'id_pegawai' => $id,
                    'nik_pegawai' => sprintf("%05d", (int) $input['nik']),
                    'nm_pegawai' => $input['nama'],
                    'alamat_pegawai' => $input['alamat'],
                    'jenis_kelamin' => $input['kelamin'],
                    'no_telp' => $input['hp'],
                    'is_active' => $input['status'],
                    'email_pegawai' => $input['email'],
                    'no_rekening' => $input['rekening'] ?? ''
                ],
            );

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
        $data = DB::table('f_data_pegawai as dp')
            ->whereRaw('dp.nik_pegawai = \'' . sprintf("%05d", (int) $id) . '\'')
            ->leftjoin('f_department as d', 'd.id_dept', '=', DB::raw('ANY(dp.id_dept)'))
            ->leftjoin('f_sub_department as sd', 'sd.id_subdept', '=', DB::raw('ANY(dp.id_subdept)'))
            ->select('dp.id_pegawai as id', 'dp.nik_pegawai as nik', 'dp.nm_pegawai as nama', 'dp.email_pegawai as email', DB::raw('json_agg(d.nm_dept) as dept'), DB::raw('json_agg(sd.nm_subdept) as subdept'), 'dp.is_active as status', 'dp.jenis_kelamin as kelamin', 'dp.alamat_pegawai as alamat', 'dp.no_telp as hp', 'dp.no_rekening as rekening')
            ->groupBy('dp.id_pegawai', 'dp.nik_pegawai', 'dp.nm_pegawai', 'dp.email_pegawai', 'dp.is_active', 'dp.jenis_kelamin', 'dp.alamat_pegawai', 'dp.no_telp', 'dp.no_rekening')
            ->first();

        $data->nik = (int) $data->nik;
        $data->dept = json_decode($data->dept)[0] !== null ? json_decode($data->dept) : [];
        $data->subdept = json_decode($data->subdept)[0] !== null ? json_decode($data->subdept) : [];

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

        $dup = DB::table('f_data_pegawai')->where('nik_pegawai', sprintf("%05d", (int) $input['nik']))->first();

        if ($dup && $dup->id_pegawai !== $id) {
            return response()->json(["status" => "error", 'message' => 'Duplicated NIK Pegawai'], 501);
        }

        DB::table('f_data_pegawai')
            ->where('id_pegawai', $id)
            ->update(
                [
                    'nik_pegawai' => sprintf("%05d", (int) $input['nik']),
                    'nm_pegawai' => $input['nama'],
                    'alamat_pegawai' => $input['alamat'],
                    'jenis_kelamin' => $input['kelamin'],
                    'no_telp' => $input['hp'],
                    'is_active' => $input['status'],
                    'email_pegawai' => $input['email'],
                    'no_rekening' => $input['rekening']
                ]
            );

        return response()->json(["status" => "success"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Karyawan::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 404);

        $data->delete();

        return response()->json(["status" => "success"], 201);
    }
}
