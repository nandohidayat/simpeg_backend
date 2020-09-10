<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\ProfilPendapatan;
use Illuminate\Http\Request;
use stdClass;

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
        $data = ProfilPendapatan::find($id);

        $keuangan = [];
        $personalia = [];

        $id = 0;
        if ($data->format_personalia) {
            foreach (json_decode($data->format_personalia) as $key => $value) {
                $temp = $this->encodeFormat($key, null, ++$id);
                foreach ($value as $childKey => $childValue) {
                    array_push($temp->elements, $this->encodeFormat($childKey, $childValue, ++$id));
                }
                array_push($personalia, $temp);
            }
        }

        if ($data->format_keuangan) {
            foreach (json_decode($data->format_keuangan) as $key => $value) {
                $temp = $this->encodeFormat($key, null, ++$id);
                foreach ($value as $childKey => $childValue) {
                    array_push($temp->elements, $this->encodeFormat($childKey, $childValue, ++$id));
                }
                array_push($keuangan, $temp);
            }
        }

        return response()->json(["status" => "success", 'data' => ['personalia' => $personalia, 'keuangan' => $keuangan, 'id' => $id]], 200);
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

        $data = ProfilPendapatan::find($id);
        $input = $request->all();

        if (isset($input['type'])) {
            $temp = new stdClass;
            foreach ($input['format'] as $f) {
                $temp->{$this->decodeFormat($f)} = new stdClass;
                foreach ($f['elements'] as $e) {
                    $temp->{$this->decodeFormat($f)}->{$this->decodeFormat($e)} = $e['value'];
                }
            }
            if ($input['type'] === 'personalia') {
                $data->format_personalia = json_encode($temp);
            }
            if ($input['type'] === 'keuangan') {
                $data->format_keuangan = json_encode($temp);
            }
        } else {
            $data->nama_pendapatan = $input['text'];
        }
        $data->save();

        return response()->json(["status" => "success", 'data' => $temp], 201);
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

    function encodeFormat($key, $value, $id)
    {
        $temp = new stdClass;
        $temp->id = $id;
        $temp->type = [];
        $type = explode(':', $key);
        if (count($type) > 1) {
            for ($i = 0; $i < count($type) - 1; $i++) {
                array_push($temp->type, $type[$i]);
            }
        }
        $temp->key = end($type);
        $temp->value = $value;
        $temp->elements = [];

        return $temp;
    }

    function decodeFormat($value)
    {
        return '' . implode(':', $value['type']) . '' . (count($value['type']) > 0 ? ':' : '') . '' . $value['key'] . '';
    }
}
