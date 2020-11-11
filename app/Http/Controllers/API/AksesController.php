<?php

namespace App\Http\Controllers\API;

use App\Akses;
use App\AksesDepartemen;
use App\AksesKategori;
use App\AksesUser;
use App\Departemen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class AksesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('f_login_pegawai as flp')
            ->join('f_data_pegawai as fdp', 'fdp.id_pegawai', '=', 'flp.id_pegawai')
            ->leftJoin('users as us', 'us.id_pegawai', '=', 'flp.id_pegawai')
            ->leftJoin('groups as gr', 'gr.id_group', '=', 'us.id_group')
            ->select('fdp.id_pegawai', 'fdp.nm_pegawai as nama', 'flp.user_pegawai as username', 'gr.id_group', 'gr.label')
            ->orderBy('fdp.nm_pegawai')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->password !== $request->confirm) {
            return response()->json(['status' => 'error', 'message' => 'Confirm is not the same with Password'], 501);
        }

        DB::select('INSERT INTO f_login_pegawai (id_pegawai, user_pegawai, pass_pegawai) VALUES (\'' . $request->id_pegawai . '\', \'' . $request->username . '\',\'' . md5($request->password) . '\')');

        DB::table('users')->insert(['id_pegawai' => $request->id_pegawai, 'id_group' => $request->id_group]);

        $data = DB::table('f_login_pegawai as flp')
            ->join('f_data_pegawai as fdp', 'fdp.id_pegawai', '=', 'flp.id_pegawai')
            ->leftJoin('users as us', 'us.id_pegawai', '=', 'flp.id_pegawai')
            ->leftJoin('groups as gr', 'gr.id_group', '=', 'us.id_group')
            ->select('fdp.id_pegawai', 'fdp.nm_pegawai as nama', 'flp.user_pegawai as username', 'gr.id_group', 'gr.label')
            ->where('flp.id_pegawai', $request->id_pegawai)
            ->orderBy('fdp.nm_pegawai')
            ->first();

        return response()->json(["status" => "success", "data" => $data], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $akses = AksesUser::where(['id_pegawai' => $id, 'status' => 'true'])
            ->pluck('id_akses');

        return response()->json(["status" => "success", "data" => $akses], 200);
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
        if ($request->password !== $request->confirm) {
            return response()->json(['status' => 'error', 'message' => 'Confirm is not the same with Password'], 501);
        }

        DB::table('f_login_pegawai')->where('id_pegawai', $id)->update(['user_pegawai' => $request->username]);

        if ($request->password !== 'undefined' && strlen($request->password) > 0) {
            DB::table('f_login_pegawai')->where('id_pegawai', $id)->update(['pass_pegawai' => md5($request->password)]);
        }

        DB::table('users')->updateOrInsert(['id_pegawai' => $id], ['id_group' => $request->id_group]);

        $data = DB::table('f_login_pegawai as flp')
            ->join('f_data_pegawai as fdp', 'fdp.id_pegawai', '=', 'flp.id_pegawai')
            ->leftJoin('users as us', 'us.id_pegawai', '=', 'flp.id_pegawai')
            ->leftJoin('groups as gr', 'gr.id_group', '=', 'us.id_group')
            ->select('fdp.id_pegawai', 'fdp.nm_pegawai as nama', 'flp.user_pegawai as username', 'gr.id_group', 'gr.label')
            ->where('flp.id_pegawai', $id)
            ->orderBy('fdp.nm_pegawai')
            ->first();

        return response()->json(["status" => "success", 'data' => $data], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('f_login_pegawai')->where('id_pegawai', $id)->delete();

        DB::table('users')->where('id_pegawai', $id)->delete();

        return response(['status' => 'success'], 201);
    }
}
