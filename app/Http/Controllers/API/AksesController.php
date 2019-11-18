<?php

namespace App\Http\Controllers\API;

use App\Akses;
use App\AksesDepartemen;
use App\AksesKategori;
use App\Departemen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $kategori = AksesKategori::with('akses')->orderBy('kategori', 'asc')->get();

        $data = [];
        foreach ($kategori as $k) {
            $obj = new stdClass();
            $obj->id = 'ketegori' . $k->id_akses_kategori;
            $obj->name = $k->kategori;
            $obj->children = [];
            foreach ($k->akses as $a) {
                $chd = new stdClass();
                $chd->id = $a->id_akses;
                $chd->name = $a->akses;
                array_push($obj->children, $chd);
            }
            array_push($data, $obj);
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
    { }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $departemen = Departemen::with('aksesDepartemens')->where('id_departemen', $id)->first();
        $data = [];

        foreach ($departemen->aksesDepartemens as $a) {
            array_push($data, $a->id_akses);
        }

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
}
