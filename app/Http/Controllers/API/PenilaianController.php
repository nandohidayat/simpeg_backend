<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Penilaian;
use App\Rekan;
use Illuminate\Support\Facades\DB;
use stdClass;
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
        $input = $request->all();

        // $validator = Validator::make($input, [
        //     'name' => 'required',
        //     'detail' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }

        $penilaian = Penilaian::create($input);

        $atasan = $penilaian->rekans()->createMany($request->atasans);
        $setingkat = $penilaian->rekans()->createMany($request->setingkats);
        $bawahan = $penilaian->rekans()->createMany($request->bawahans);

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
    public function update(Request $request, Penilaian $penilaian)
    {
        // $input = $request->all();

        // $validator = Validator::make($input, [
        //     'name' => 'required',
        //     'detail' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }

        $penilaian->pegawais_id = $request->pegawais_id;
        $penilaian->mulai = $request->mulai;
        $penilaian->selesai = $request->selesai;
        $penilaian->save();

        $this->updateRekans($request->atasans);
        $this->updateRekans($request->setingkats);
        $this->updateRekans($request->bawahans);

        return $this->sendResponse([], 'Product created successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Penilaian $penilaian)
    {
        $penilaian->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
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

    protected function updateRekans($rekans)
    {
        foreach ($rekans as $rekan) {
            $data = Rekan::find($rekan['id']);
            if ($rekan['pegawais_id'] != $data->pegawais_id) {
                $data->pegawais_id = $rekan['pegawais_id'];
                $data->done = false;
            }
            $data->save();
        }
    }
}
