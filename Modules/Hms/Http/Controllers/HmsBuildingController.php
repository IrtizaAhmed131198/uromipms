<?php

namespace Modules\Hms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Hms\Entities\HmsBuilding;
use Yajra\DataTables\Facades\DataTables;

class HmsBuildingController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $buildings = HmsBuilding::where('business_id', $business_id)
                ->withCount('floors');

            return DataTables::of($buildings)
                ->addColumn('action', function ($row) {
                    $html = '<button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit-building-btn"
                        data-id="' . $row->id . '"
                        data-name="' . e($row->name) . '"
                        data-location="' . e($row->location) . '">'
                        . __('messages.edit') . '</button>';
                    $html .= ' <button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error delete-building-btn"
                        data-id="' . $row->id . '">'
                        . __('messages.delete') . '</button>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('hms::buildings.index');
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $request->validate([
            'name' => [
                'required', 'string', 'max:191',
                \Illuminate\Validation\Rule::unique('hms_buildings')->where('business_id', $business_id),
            ],
            'location' => 'nullable|string',
        ]);

        HmsBuilding::create([
            'business_id' => $business_id,
            'name'        => $request->name,
            'location'    => $request->location,
        ]);

        return response()->json(['success' => true, 'msg' => __('messages.added_successfully')]);
    }

    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        $request->validate([
            'name' => [
                'required', 'string', 'max:191',
                \Illuminate\Validation\Rule::unique('hms_buildings')->where('business_id', $business_id)->ignore($id),
            ],
            'location' => 'nullable|string',
        ]);

        HmsBuilding::where('business_id', $business_id)->findOrFail($id)->update([
            'name'     => $request->name,
            'location' => $request->location,
        ]);

        return response()->json(['success' => true, 'msg' => __('messages.updated_successfully')]);
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $building = HmsBuilding::where('business_id', $business_id)->withCount('floors')->findOrFail($id);

        if ($building->floors_count > 0) {
            return response()->json(['success' => false, 'msg' => __('hms::lang.building_has_floors')]);
        }

        $building->delete();

        return response()->json(['success' => true, 'msg' => __('messages.deleted_successfully')]);
    }
}
