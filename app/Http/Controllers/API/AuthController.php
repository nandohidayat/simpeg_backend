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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AuthController extends BaseController
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request)
    {
        // $credentials = request(['username', 'password']);

        // $credentials = [
        //     'user_pegawai' => $request->username,
        //     'password' => md5($request->password)
        // ];

        // error_log(json_encode($credentials));

        // if (!Auth::attempt($credentials))
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ], 401);

        $user = SIMLoginPegawai::where([
            'user_pegawai' => $request->username,
            'pass_pegawai' => md5($request->password)
        ])->first();

        if ($user == null)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        if ($user->token_sp == null) {
            $user->token_sp = Str::random(80);
            $user->save();
        }

        Auth::login($user);

        // $user = Auth::user();
        // $tokenResult = $user->createToken('Personal Access Token');
        // $token = $tokenResult->token;

        // $token->expires_at = Carbon::now()->addDay();
        // $token->save();

        // $id = Karyawan::where('nik', $user->nik)->first()->id_departemen;

        // $hakAkses = AksesDepartemen::where([['id_departemen', '=', $id], ['status', '=', 'true']])
        //     ->join('akses', 'akses_departemens.id_akses', '=', 'akses.id_akses')
        //     ->join('akses_kategoris', 'akses.id_akses_kategori', '=', 'akses_kategoris.id_akses_kategori')
        //     ->orderBy('akses.id_akses', 'asc')
        //     ->select('akses.akses', 'akses.url', 'akses_kategoris.kategori', 'akses_kategoris.icon')
        //     ->get();

        // $menu = [];
        // $akses = [];
        // $i = 0;
        // $before = "";
        // foreach ($hakAkses as $h) {
        //     if ($before !== $h->kategori) {
        //         $i++;
        //         $before = $h->kategori;
        //         $menu[$i] = new stdClass();
        //         $menu[$i]->icon = $h->icon;
        //         $menu[$i]->header = $h->kategori;
        //         $menu[$i]->children = [];
        //     }
        //     $obj = new stdClass();
        //     $obj->header = $h->akses;
        //     $obj->link = $h->url;
        //     array_push($menu[$i]->children, $obj);
        //     array_push($akses, $h->url);
        // }

        return response()->json([
            // 'token' => $tokenResult->accessToken,
            'token' => $user->token_sp,
            // 'expires_at' => Carbon::parse(
            //     $tokenResult->token->expires_at
            // )->toDateTimeString(),
            'user' => json_encode($user)
            // 'user' => ['nik' => $user->nik, 'username' => $user->username],
            // 'menu' => $menu,
            // 'akses' => $akses
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
