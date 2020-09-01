<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\ProfilPendapatan;
use Illuminate\Http\Request;

class ProfilPendapatanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];

        if ((int) request()->select === 1) {
            $data = ProfilPendapatan::select('id_profilp as value', 'nama_pendapatan as text')->orderBy('text')->get();
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

        $data = new ProfilPendapatan;
        $data->nama_pendapatan = $input['text'];
        $data->save();

        return response()->json(["status" => "success", 'data' => $data], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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

        $data = ProfilPendapatan::find($id);
        $data->nama_pendapatan = $input['text'];
        $data->save();

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
        $data = ProfilPendapatan::find($id);
        $data->delete();

        return response()->json(["status" => "success"], 201);
    }
}
