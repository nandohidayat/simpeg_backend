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
        $user = SIMLoginPegawai::where([
            'user_pegawai' => $request->username,
            'pass_pegawai' => md5($request->password)
        ])
            ->join('f_data_pegawai as fdp', function ($join) {
                $join->on('fdp.id_pegawai', '=', 'f_login_pegawai.id_pegawai');
                $join->where('fdp.is_active', 'true');
            })
            ->leftJoin('f_department as fd', 'fd.kepala_dept', '=', 'f_login_pegawai.id_pegawai')
            ->select('f_login_pegawai.id_pegawai', 'fdp.nik_pegawai as nik', 'f_login_pegawai.user_pegawai', 'fdp.id_dept', 'fdp.id_subdept', DB::raw('(CASE WHEN fd.id_dept IS NOT NULL THEN true ELSE false END) as kepala'))
            ->first();

        if ($user == null)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $token = auth()->login($user);

        $dataAkses = DB::table('f_data_pegawai as fdp')
            ->where('fdp.id_pegawai', auth()->user()->id_pegawai)
            ->join('akses_departemens as ad', function ($join) {
                $join->on('ad.id_dept', '=', DB::raw('ANY(\'' . auth()->user()->id_dept . '\')'));
                $join->where('ad.status', 'true');
                if (!auth()->user()->kepala) {
                    $join->where('ad.only', 'false');
                }
            })
            ->join('akses as a', 'a.id_akses', '=', 'ad.id_akses')
            ->join('akses_kategoris as ak', 'ak.id_akses_kategori', '=', 'a.id_akses_kategori')
            ->select('a.akses', 'a.url', 'ak.kategori', 'ak.icon')
            ->groupBy('a.akses', 'a.url', 'ak.kategori', 'ak.icon', 'a.id_akses')
            ->orderBy('a.id_akses')
            ->get();

        $menu = [];
        $akses = [];
        $i = -1;
        $before = null;
        foreach ($dataAkses as $da) {
            if ($before !== $da->kategori) {
                $i++;
                $before = $da->kategori;
                $obj = new stdClass();
                $obj->icon = $da->icon;
                $obj->header = $da->kategori;
                $obj->children = [];
                array_push($menu, $obj);
            }
            $obj = new stdClass();
            $obj->header = $da->akses;
            $obj->link = $da->url;
            array_push($menu[$i]->children, $obj);
            array_push($akses, $da->url);
        }

        return response()->json([
            'token' => $token,
            'expires_at' => auth()->factory()->getTTL(),
            'user' => [
                'id' => auth()->user()->id_pegawai,
                'nik' => auth()->user()->nik,
                'username' => auth()->user()->user_pegawai,
            ],
            'menu' => $menu,
            'akses' => $akses
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
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user($id)
    {
        $data = DB::table('f_data_pegawai as dp')
            ->where('nik_pegawai', $id)
            ->join('f_login_pegawai as lp', 'lp.id_pegawai', '=', 'dp.id_pegawai')
            ->select('lp.user_pegawai as username')
            ->first();
        // $data = User::where('nik', $id)->select('username')->first();

        return response()->json(["status" => "success", "data" => $data], 200);
    }
}
