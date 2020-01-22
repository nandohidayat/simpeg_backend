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
        ])->first();

        if ($user == null)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $token = auth()->login($user);

        $dataAkses = DB::connection('pgsql')
            ->table('f_login_pegawai as flp')
            ->where('flp.id_pegawai', auth()->user()->id_pegawai)
            ->join('akses_departemens as ad', function ($join) {
                $join->on('ad.id_dept', '=', DB::raw('ANY(\'' . auth()->user()->id_dept . '\')'));
                $join->where('ad.status', 'true');
            })
            ->join('akses as a', 'a.id_akses', '=', 'ad.id_akses')
            ->join('akses_kategoris as ak', 'ak.id_akses_kategori', '=', 'a.id_akses_kategori')
            ->select('a.akses', 'a.url', 'ak.kategori', 'ak.icon')
            ->get();

        $menu = [];
        $akses = [];
        $i = -1;
        $before = "";
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
                'username' => auth()->user()->user_pegawai
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
        $user = User::where('nik', Auth::user()->nik)->first();

        if (Hash::check($request['current'], $user->password)) {
            if ($request['password'] === null) {
                User::updateOrCreate(
                    ['nik' => $request['nik']],
                    [
                        "username" => $request['username'],
                    ]
                );
            } else {
                User::updateOrCreate(
                    ['nik' => $request['nik']],
                    [
                        "username" => $request['username'],
                        "password" => bcrypt($request['password'])
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
        $data = User::where('nik', $id)->select('username')->first();

        return response()->json(["status" => "success", "data" => $data], 200);
    }
}
