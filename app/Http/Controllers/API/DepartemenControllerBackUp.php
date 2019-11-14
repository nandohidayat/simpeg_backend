<?php


namespace App\Http\Controllers\API;

use App\Departemen;
use App\Bagian;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;


class DepartemenController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Departemen::orderBy('departemen', 'asc')->get();

        return $this->sendResponse($data, 'Product retrieved successfully.');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $departemen = Departemen::orderBy('tingkat', 'desc')->first();
        $tingkat = $departemen->tingkat;

        $request['tingkat'] = $tingkat + 1;
        $input = $request->all();
        $data = Departemen::create($input);

        return $this->sendResponse($data, 'Sukses mang, yeyeyeyeye');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    { }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Penilaian $penilaian)
    { }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bagian $bagian)
    {
        error_log($bagian);
        $bagian->delete();
        return $this->sendResponse([], 'Sukses mang, yeyeyeyeye');
    }
}
