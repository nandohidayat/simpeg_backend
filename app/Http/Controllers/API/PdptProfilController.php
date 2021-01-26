<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\PendapatanProfil;
use Illuminate\Http\Request;

class PdptProfilController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = PendapatanProfil::all();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new PendapatanProfil();
        $data->title = $request->title;
        $data->view = $request->view;
        $data->save();

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = PendapatanProfil::all();

        return response()->json(['status' => 'success', 'data' => $data]);
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
        $data = PendapatanProfil::find($id);
        $data->title = $request->title;
        $data->view = $request->view;
        $data->save();

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = PendapatanProfil::find($id);
        $data->delete();

        return response()->json(['status' => 'success']);
    }
}
