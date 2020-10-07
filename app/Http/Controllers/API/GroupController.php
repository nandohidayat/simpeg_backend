<?php

namespace App\Http\Controllers\API;

use App\AksesGroup;
use App\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Group::select('id_group', 'label')->get();
        return response(['status' => 'success', 'data' => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new Group();
        $data->label = $request->label;
        $data->save();

        return response(['status' => 'success', 'data' => $data], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Group::where('id_group', $id)->select('label')->first();
        if ($data === null) {
            return response(['status' => 'error', 'message' => 'Group not found, try to refresh the pages'], 500);
        }
        $data->akses = AksesGroup::where([['id_group', '=', $id], ['status', '=', 'true']])->pluck('id_akses');

        return response(['status' => 'success', 'data' => $data], 200);
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
        $data = Group::find($id);
        $data->label = $request->label;
        $data->save();

        return response(['status' => 'success'], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Group::find($id);
        $data->delete();

        return response(['status' => 'success'], 201);
    }
}
