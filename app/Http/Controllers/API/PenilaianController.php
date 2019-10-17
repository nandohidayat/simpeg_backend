<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Penilaian;
use App\Rekan;
use Illuminate\Support\Facades\DB;
use Validator;


class PenilaianController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = DB::table('penilaians')
        //     ->join('pegawais', 'penilaians.pegawais_id', '=', 'pegawais.id')
        //     ->select('penilaians.id', 'penilaians.mulai', 'penilaians.selesai', 'pegawais.nik', 'pegawais.nama')
        //     ->get();

        $data = Penilaian::select(['id', 'mulai', 'selesai', 'pegawais_id'])->with('pegawais', 'atasans', 'setingkats', 'bawahans')->get();
        // $data = Rekan::with('pegawais')->get();

        return $this->sendResponse($data->toArray(), 'Data retrieved successfully.');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $input = $request->all();

        // $validator = Validator::make($input, [
        //     'name' => 'required',
        //     'detail' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }

        $penilaian = new Penilaian;
        $penilaian->pegawais_id = $request->pegawai_id;
        $penilaian->mulai = $request->mulai;
        $penilaian->selesai = $request->selesai;
        $penilaian->save();

        foreach ($request->atasan as $a) {
            $rekan = new Rekan;
            $rekan->penilaian_id = $penilaian->id;
            $rekan->pegawais_id = $a;
            $rekan->tingkat = 1;
            $rekan->save();
        }

        foreach ($request->setingkat as $a) {
            $rekan = new Rekan;
            $rekan->penilaian_id = $penilaian->id;
            $rekan->pegawais_id = $a;
            $rekan->tingkat = 2;
            $rekan->save();
        }
        foreach ($request->bawahan as $a) {
            $rekan = new Rekan;
            $rekan->penilaian_id = $penilaian->id;
            $rekan->pegawais_id = $a;
            $rekan->tingkat = 3;
            $rekan->save();
        }

        return $this->sendResponse([], 'Product created successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Penilaian::where('id', '=', $id)
            ->select(['id', 'mulai', 'selesai', 'pegawais_id'])
            ->with('pegawais', 'atasans', 'setingkats', 'bawahans')
            ->get();

        // if (is_null($product)) {
        //     return $this->sendError('Product not found.');
        // }

        return $this->sendResponse($data->toArray(), 'Product retrieved successfully.');
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

    public function updateDetail($id)
    {
        # code...
        $data = Penilaian::where('id', '=', $id)
            ->select(['id', 'mulai', 'selesai', 'pegawais_id'])
            ->with('atasans', 'setingkats', 'bawahans')
            ->first();

        return $this->sendResponse($data, 'Product retrieved successfully.');
    }
}
