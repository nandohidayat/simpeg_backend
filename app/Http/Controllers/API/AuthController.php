<?php


namespace App\Http\Controllers\API;

use App\Akses;
use App\AksesDepartemen;
use App\AksesKategori;
use App\Departemen;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Karyawan;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use stdClass;
use Validator;


class AuthController extends BaseController
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request)
    {
        $credentials = request(['username', 'password']);
        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        $id = Karyawan::where('nik', $user->nik)->first()->id_departemen;

        $hakAkses = AksesDepartemen::where([['id_departemen', '=', $id], ['status', '=', 'true']])
            ->join('akses', 'akses_departemens.id_akses', '=', 'akses.id_akses')
            ->join('akses_kategoris', 'akses.id_akses_kategori', '=', 'akses_kategoris.id_akses_kategori')
            ->select('akses.akses', 'akses.url', 'akses_kategoris.kategori', 'akses_kategoris.icon')
            ->groupBy('akses.akses', 'akses.url', 'akses.id_akses_kategori')
            ->get();

        error_log($hakAkses);

        $departemen = Departemen::with('aksesDepartemens')->where('id_departemen', $id)->first();
        $hak = [];

        foreach ($departemen->aksesDepartemens as $a) {
            array_push($hak, $a->id_akses);
        }

        $list_akses = AksesKategori::with('akses')->get();
        $akses = [];

        foreach ($list_akses as $p) {
            $parent = new stdClass();
            $parent->icon = $p->icon;
            $parent->header = $p->kategori;
            $parent->children = [];
            foreach ($p->akses as $c) {
                $children = new stdClass();
                $children->header = $c->akses;
                $children->link = $c->url;
                array_push($parent->children, $children);
            }
        }

        return response()->json([
            'token' => $tokenResult->accessToken,
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => ['nik' => $user->nik, 'username' => $user->username]
        ]);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $user = new User([
            'username' => $request->username,
            'password' => bcrypt($request->password)
        ]);

        $user->save();

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
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
