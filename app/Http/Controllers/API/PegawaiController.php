<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Pegawai;
use App\SIMDataPegawai;
use Illuminate\Support\Facades\DB;
use Mockery\Undefined;
use stdClass;
use Validator;


class PegawaiController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = null;

        if ((int) request()->select === 1) {
            $data = SIMDataPegawai::whereRaw('\'' . request()->dept . '\' = ANY(id_dept)')
                ->where('is_active', true)
                ->select('id_pegawai', 'nm_pegawai')
                ->get();
        } else {
            $data = SIMDataPegawai::select('id_pegawai')->get();
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


        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);


        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $product = Product::create($input);


        return $this->sendResponse($product->toArray(), 'Product created successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pegawai = Pegawai::with('jabatans')->where('id', '=', $id)->first();

        $data = new stdClass();
        $data->atasans = Pegawai::whereHas('jabatans', function ($query) use ($pegawai) {
            $query->where('bagian', '=', $pegawai->jabatans->bagian)
                ->where('tingkat', '=', $pegawai->jabatans->tingkat - 1);
        })->select('id')->get();
        $data->setingkats = Pegawai::whereHas('jabatans', function ($query) use ($pegawai) {
            $query->where('bagian', '=', $pegawai->jabatans->bagian)
                ->where('tingkat', '=', $pegawai->jabatans->tingkat);
        })->where('id', '!=', $id)->select('id')->take(3)->get();
        $data->bawahans = Pegawai::whereHas('jabatans', function ($query) use ($pegawai) {
            $query->where('bagian', '=', $pegawai->jabatans->bagian)
                ->where('tingkat', '=', $pegawai->jabatans->tingkat + 1);
        })->select('id')->take(3)->get();

        // if (is_null($data)) {
        //     return $this->sendError('Product not found.');
        // }

        return $this->sendResponse($data, 'Product retrieved successfully.');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();


        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);


        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $product->name = $input['name'];
        $product->detail = $input['detail'];
        $product->save();


        return $this->sendResponse($product->toArray(), 'Product updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();


        return $this->sendResponse($product->toArray(), 'Product deleted successfully.');
    }
}
