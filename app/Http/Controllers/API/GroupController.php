<?php

namespace App\Http\Controllers\API;

use App\Akses;
use App\AksesGroup;
use App\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::select(
            "SELECT id_group, label, array_to_json(array_agg(permission)) as permission, array_to_json(array_agg(DISTINCT akses)) as akses
            FROM (
                SELECT g.id_group, g.label, array_agg(a.id_akses) as perm, jsonb_build_object('menu', am.menu, 'akses', array_agg(a.akses ORDER BY a.akses)) as akses
                FROM groups as g
                JOIN akses_groups as ag ON g.id_group = ag.id_group
                JOIN akses as a ON a.id_akses = ag.id_akses
                JOIN akses_submenus as asm ON asm.id_akses_submenu = a.id_akses_submenu
                JOIN akses_menus as am ON am.id_akses_menu = asm.id_akses_menu
                WHERE ag.status = true
                GROUP BY g.id_group, g.label, am.menu
                ORDER BY am.menu
            ) a, unnest(perm) as permission
            GROUP BY a.id_group, a.label"
        );

        foreach ($data as $key => $value) {
            $data[$key]->permission = json_decode($data[$key]->permission);
            $data[$key]->akses = json_decode($data[$key]->akses);
        }

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
        $request->permission = array_map('intval', $request->permission);

        $group = new Group();
        $group->label = $request->label;
        $group->save();

        $query = 'INSERT INTO akses_groups (id_group, id_akses, status) VALUES ';

        $akses = Akses::all();
        foreach ($akses as $a) {
            $query .= '(\'' . $group->id_group . '\', \'' . $a->id_akses . '\', ' . (in_array((int) $a->id_akses, $request->permission, true) ? 'true' : 'false') . '), ';
        }

        $query = substr($query, 0, -2);

        $query .= ';';

        DB::select($query);

        $data = DB::select(
            "SELECT id_group, label, array_to_json(array_agg(permission)) as permission, array_to_json(array_agg(DISTINCT akses)) as akses
            FROM (
                SELECT g.id_group, g.label, array_agg(a.id_akses) as perm, jsonb_build_object('menu', am.menu, 'akses', array_agg(a.akses ORDER BY a.akses)) as akses
                FROM groups as g
                JOIN akses_groups as ag ON g.id_group = ag.id_group
                JOIN akses as a ON a.id_akses = ag.id_akses
                JOIN akses_submenus as asm ON asm.id_akses_submenu = a.id_akses_submenu
                JOIN akses_menus as am ON am.id_akses_menu = asm.id_akses_menu
                WHERE g.id_group = " . $group->id_group . " AND ag.status = true
                GROUP BY g.id_group, g.label, am.menu
                ORDER BY am.menu
            ) a, unnest(perm) as permission
            GROUP BY a.id_group, a.label"
        )[0];


        $data->permission = json_decode($data->permission);
        $data->akses = json_decode($data->akses);

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
        $request->permission = array_map('intval', $request->permission);

        $query = 'INSERT INTO akses_groups (id_group, id_akses, status) VALUES ';

        $group = Group::find($id);
        $group->label = $request->label;
        $group->save();

        $akses = Akses::all();
        foreach ($akses as $a) {
            $query .= '(\'' . $id . '\', \'' . $a->id_akses . '\', ' . (in_array((int) $a->id_akses, $request->permission, true) ? 'true' : 'false') . '), ';
        }

        $query = substr($query, 0, -2);

        $query .= ' ON CONFLICT ON CONSTRAINT akses_groups_ukey DO UPDATE SET status = excluded.status;';

        DB::select($query);

        $data = DB::select(
            "SELECT id_group, label, array_to_json(array_agg(permission)) as permission, array_to_json(array_agg(DISTINCT akses)) as akses
            FROM (
                SELECT g.id_group, g.label, array_agg(a.id_akses) as perm, jsonb_build_object('menu', am.menu, 'akses', array_agg(a.akses ORDER BY a.akses)) as akses
                FROM groups as g
                JOIN akses_groups as ag ON g.id_group = ag.id_group
                JOIN akses as a ON a.id_akses = ag.id_akses
                JOIN akses_submenus as asm ON asm.id_akses_submenu = a.id_akses_submenu
                JOIN akses_menus as am ON am.id_akses_menu = asm.id_akses_menu
                WHERE g.id_group = " . $id . " AND ag.status = true
                GROUP BY g.id_group, g.label, am.menu
                ORDER BY am.menu
            ) a, unnest(perm) as permission
            GROUP BY a.id_group, a.label"
        )[0];


        $data->permission = json_decode($data->permission);
        $data->akses = json_decode($data->akses);

        return response(['status' => 'success', 'data' => $data], 201);
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
