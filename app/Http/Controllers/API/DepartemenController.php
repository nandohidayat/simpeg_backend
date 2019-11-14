<?php

namespace App\Http\Controllers\API;

use App\Departemen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Departemen::orderBy('departemen', 'asc')->get();

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
        $departemen = Departemen::where('id_bagian', '=', $request['id_bagian'])->orderBy('tingkat', 'desc')->first();
        $tingkat = $departemen->tingkat;

        $request['tingkat'] = $tingkat + 1;

        $input = $request->all();
        $data = Departemen::create($input);

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $data = Departemen::updateOrCreate(['id_departemen' => $id], $request->all());

        if ($data === null) return response()->json(["status" => "failed"], 501);
        return response()->json(["status" => "success"], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Departemen::find($id);
        if ($data === null) return response()->json(["status" => "not found"], 404);

        $data->delete();
        return response()->json(["status" => "success"], 201);
    }
}
