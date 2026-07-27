<?php

namespace Modules\Hms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Hms\Entities\HmsBuilding;
use Modules\Hms\Entities\HmsFloor;
use Yajra\DataTables\Facades\DataTables;

class HmsFloorController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $floors = HmsFloor::where('hms_floors.business_id', $business_id)
                ->join('hms_buildings', 'hms_floors.hms_building_id', '=', 'hms_buildings.id')
                ->select('hms_floors.*', 'hms_buildings.name as building_name');

            return DataTables::of($floors)
                ->addColumn('action', function ($row) {
                    $html = '<button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit-floor-btn"
                        data-id="' . $row->id . '"
                        data-name="' . e($row->name) . '"
                        data-building-id="' . $row->hms_building_id . '">'
                        . __('messages.edit') . '</button>';
                    $html .= ' <button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error delete-floor-btn"
                        data-id="' . $row->id . '">'
                        . __('messages.delete') . '</button>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $buildings = HmsBuilding::where('business_id', $business_id)->pluck('name', 'id');

        return view('hms::floors.index', compact('buildings'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $request->validate([
            'hms_building_id' => 'required|exists:hms_buildings,id',
            'name'            => [
                'required', 'string', 'max:191',
                \Illuminate\Validation\Rule::unique('hms_floors')->where(function ($q) use ($request) {
                    return $q->where('hms_building_id', $request->hms_building_id);
                }),
            ],
        ]);

        HmsFloor::create([
            'business_id'     => $business_id,
            'hms_building_id' => $request->hms_building_id,
            'name'            => $request->name,
        ]);

        return response()->json(['success' => true, 'msg' => __('messages.added_successfully')]);
    }

    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        $request->validate([
            'hms_building_id' => 'required|exists:hms_buildings,id',
            'name'            => [
                'required', 'string', 'max:191',
                \Illuminate\Validation\Rule::unique('hms_floors')->where(function ($q) use ($request) {
                    return $q->where('hms_building_id', $request->hms_building_id);
                })->ignore($id),
            ],
        ]);

        HmsFloor::where('business_id', $business_id)->findOrFail($id)->update([
            'hms_building_id' => $request->hms_building_id,
            'name'            => $request->name,
        ]);

        return response()->json(['success' => true, 'msg' => __('messages.updated_successfully')]);
    }

    public function getByBuilding(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $floors = HmsFloor::where('business_id', $business_id)
            ->where('hms_building_id', $request->building_id)
            ->pluck('name', 'id');
        return response()->json($floors);
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        HmsFloor::where('business_id', $business_id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'msg' => __('messages.deleted_successfully')]);
    }
}
