<?php

namespace App\Http\Controllers\API;

use App\Absen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AbsenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Absen::all();

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $data = Bagian::updateOrCreate(['id_bagian' => $id], $request->all());

        if ($data === null) return response()->json(["status" => "failed"], 501);
        return response()->json(["status" => "success"], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $firstday = Carbon::create(request()->tahun, request()->bulan)->firstOfMonth();
        $lastday = Carbon::create(request()->tahun, request()->bulan)->lastOfMonth();

        $data = Absen::where('nik', $id)
            ->whereBetween('tgl', [$firstday, $lastday])
            ->select('id_absen', 'nik', 'type', 'tgl', 'waktu')
            ->get();

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
