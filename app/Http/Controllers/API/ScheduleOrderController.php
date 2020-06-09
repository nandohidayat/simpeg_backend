<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\ScheduleOrder;
use Illuminate\Http\Request;

class ScheduleOrderController extends Controller
{
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $data = ScheduleOrder::updateOrCreate(['id_dept' => $id], ['order' => $input['order']]);

        if ($data === null) return response()->json(["status" => "failed"], 501);

        return response()->json(["status" => "success"], 201);
    }
}
