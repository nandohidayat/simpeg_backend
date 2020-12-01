<?php


namespace App\Http\Controllers\API;

use App\Akses;
use App\AksesDepartemen;
use App\AksesKategori;
use App\Departemen;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Karyawan;
use App\ShiftDepartemen;
use App\SIMDataPegawai;
use App\SIMLoginPegawai;
use App\User;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use stdClass;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request)
    {
        $user = SIMDataPegawai::rightJoin('f_login_pegawai', function ($query) use ($request) {
            $query->on('f_data_pegawai.id_pegawai', '=', 'f_login_pegawai.id_pegawai');
            $query->where([
                'user_pegawai' => $request->username,
                'pass_pegawai' => md5($request->password)
            ]);
        })
            ->where('f_data_pegawai.is_active', 'true')
            ->first();

        if ($user == null)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $token = auth()->setTTL(86400)->login($user);

        return response()->json([
            'token' => $token,
        ]);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $user = SIMLoginPegawai::where(['id_pegawai' => auth()->user()->id, 'pass_pegawai' => md5($request['current'])])
            ->first();
        // $user = User::where('nik', Auth::user()->nik)->first();

        if ($user !== null) {
            if ($request['password'] === null) {
                DB::connection('pgsql2')
                    ->table('login_pegawai')
                    ->updateOrCreate(
                        ['id_pegawai' => $request['nik']],
                        [
                            "user_pegawai" => $request['username'],
                        ]
                    );
            } else {
                DB::connection('pgsql2')
                    ->table('login_pegawai')
                    ->updateOrCreate(
                        ['id_pegawai' => $request['nik']],
                        [
                            "user_pegawai" => $request['username'],
                            "pass_pegawai" => md5($request['password'])
                        ]
                    );
            }
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user()
    {
        $today = Carbon::now(new DateTimeZone('Asia/Jakarta'))->toDateString();

        $user = DB::table('f_data_pegawai as fdp')
            ->leftJoin('log_departemens as ld', 'ld.id_pegawai', '=', 'fdp.id_pegawai')
            ->where('fdp.id_pegawai', auth()->user()->id_pegawai)
            ->whereRaw('ld.masuk <= \'' . $today . '\'')
            ->whereRaw('coalesce(ld.keluar, \'' . $today . '\') >= \'' . $today . '\'')
            ->select('fdp.id_pegawai', 'fdp.nik_pegawai as nik', 'fdp.nm_pegawai as nama', DB::raw('jsonb_agg(ld.id_dept) as dept'))
            ->groupBy('fdp.id_pegawai', 'nik', 'nama')
            ->first();

        $dataAkses = DB::table('users as us')
            ->join('akses_groups as ag', 'ag.id_group', '=', 'us.id_group')
            ->join('akses as ak', 'ak.id_akses', '=', 'ag.id_akses')
            ->join('akses_submenus as asm', 'asm.id_akses_submenu', '=', 'ak.id_akses_submenu')
            ->join('akses_menus as am', 'am.id_akses_menu', '=', 'asm.id_akses_menu')
            ->select('am.id_akses_menu', 'asm.id_akses_submenu', 'ak.id_akses')
            ->where('us.id_pegawai', auth()->user()->id_pegawai)
            ->where('ag.status', true)
            ->orderBy('id_akses_menu', 'asc')
            ->orderBy('id_akses_submenu', 'asc')
            ->orderBy('id_akses', 'asc')
            ->get();

        $menu = [];
        $submenu = [];
        $akses = [];

        foreach ($dataAkses as $d) {
            if (!in_array($d->id_akses_menu, $menu)) {
                array_push($menu, $d->id_akses_menu);
            }
            if (!in_array($d->id_akses_submenu, $submenu)) {
                array_push($submenu, $d->id_akses_submenu);
            }
            array_push($akses, $d->id_akses);
        }

        $user->menu = $menu;
        $user->submenu = $submenu;
        $user->akses = $akses;
        $user->nik = (int) $user->nik;
        $user->dept = json_decode($user->dept);

        return response()->json(["status" => "success", "user" => $user], 200);
    }

    public function refresh()
    {
        $token = auth()->refresh();

        return response()->json(["token" => $token], 200);
    }

    public function reset($id)
    {
        DB::connection('pgsql2')
            ->table('login_pegawai')
            ->where('id_pegawai', $id)
            ->update(['pass_pegawai' => md5(1234)]);

        return response()->json(["status" => 'success'], 200);
    }

    public function password(Request $request, $id)
    {
        if (empty($request->current)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 500);
        }

        $user = SIMLoginPegawai::where([
            'id_pegawai' => $id,
            'pass_pegawai' => md5($request->current)
        ])
            ->first();

        if ($user == null)
            return response()->json([
                'message' => 'Unauthorized'
            ], 500);

        $password = md5($request->password);

        SIMLoginPegawai::where('id_pegawai', $id)->update(['pass_pegawai' => $password]);

        return response()->json(["status" => 'success'], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = SIMLoginPegawai::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 401);

        $data->delete();
        return response()->json(["status" => "success"], 201);
    }

    public function try()
    {
        $data = DB::connection('pgsql3')->table('appmenu')->get();

        return response()->json(["status" => "success", 'data' => $data], 200);
    }
}
