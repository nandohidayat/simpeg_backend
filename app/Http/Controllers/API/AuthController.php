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
        $user = SIMDataPegawai::where('id_pegawai', auth()->user()->id_pegawai)
            ->leftJoin('f_department as fd', 'fd.kepala_dept', '=', 'f_data_pegawai.id_pegawai')
            ->select('f_data_pegawai.id_pegawai', 'f_data_pegawai.nik_pegawai as nik', 'f_data_pegawai.nm_pegawai as nama', 'f_data_pegawai.id_dept', 'f_data_pegawai.id_subdept')
            ->first();

        $dataAkses = DB::table('akses_users as au')
            ->where('id_pegawai', auth()->user()->id_pegawai)
            ->where('status', true)
            ->join('akses as a', 'a.id_akses', '=', 'au.id_akses')
            ->select('a.id_akses_submenu as id_akses_kategori', 'a.id_akses')
            ->get();

        $menu = [];
        $akses = [];

        foreach ($dataAkses as $d) {
            if (!in_array($d->id_akses_kategori, $menu)) {
                array_push($menu, $d->id_akses_kategori);
            }
            array_push($akses, $d->id_akses);
        }

        $user->menu = $menu;
        $user->akses = $akses;
        $user->nik = (int) $user->nik;

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
