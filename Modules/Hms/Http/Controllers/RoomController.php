<?php

namespace Modules\Hms\Http\Controllers;

use App\Charts\CommonChart;
use App\Utils\ModuleUtil;
use App\Category;
use App\Media;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Hms\Entities\HmsBookingLine;
use Modules\Hms\Entities\HmsBuilding;
use Modules\Hms\Entities\HmsRoom;
use Modules\Hms\Entities\HmsRoomType;
use Modules\Hms\Entities\HmsRoomTypePricing;
use Modules\Hms\Entities\HmsTransactionClass;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $types = HmsRoomType::where('business_id', $business_id);
            return Datatables::of($types)
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->addColumn('action', function ($row) {
                    $html = '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary " href="' . action([\Modules\Hms\Http\Controllers\RoomController::class, 'edit'], ['room' => $row->id]) . '">'
                        . __('hms::lang.edit_room') . '</a>';
                    $html .= ' <a href="' . url('hms/room/' . $row->id . '/destroy') . '"
                    class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-error delete_room_confirmation">' . __('messages.delete') . '</a>';
                    if (auth()->user()->can('hms.manage_price')) {
                        $html .= ' <a href="' . action([\Modules\Hms\Http\Controllers\RoomController::class, 'pricing']) . '?room_id=' . $row->id . '"
                    class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info ">' . __('hms::lang.pricing') . '</a>';
                    }

                    return $html;
                })
                ->editColumn('description', '{!! $description !!}')
                ->rawColumns(['created_at', 'action', 'description'])
                ->make(true);
        }
        return view('hms::rooms.index');
    }

    protected function room_count($status = 'confirmed')
    {
        $business_id = request()->session()->get('user.business_id');
        $today = Carbon::now();

        return Transaction::join('hms_booking_lines', 'transactions.id', '=', 'hms_booking_lines.transaction_id')
            ->where('transactions.type', 'hms_booking')
            ->where('transactions.status', $status)
            ->where('transactions.business_id', $business_id)
            ->whereDate('transactions.hms_booking_arrival_date_time', '<=', $today)
            ->whereDate('transactions.hms_booking_departure_date_time', '>=', $today)
            ->distinct('hms_booking_lines.hms_room_id')
            ->count('hms_booking_lines.hms_room_id');
    }

    public function room_status()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('hms.room_status') && !auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }

        /*
         * |------------------------------------------------------
         * | Bookings per room (confirmed). Determine check-in status per room.
         * |------------------------------------------------------
         */
        $room_bookings = Transaction::join(
            'hms_booking_lines',
            'transactions.id',
            '=',
            'hms_booking_lines.transaction_id'
        )
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'hms_booking')
            ->where('transactions.status', 'confirmed')
            ->whereNull('transactions.check_out')
            ->select(
                'transactions.id as transaction_id',
                'hms_booking_lines.hms_room_id',
                'transactions.hms_booking_arrival_date_time',
                'transactions.hms_booking_departure_date_time',
                'transactions.check_in',
                'transactions.check_out'
            )
            ->orderBy('transactions.created_at', 'desc')
            ->get()
            ->keyBy('hms_room_id');

        /*
         * |------------------------------------------------------
         * | Load rooms + roomType + media
         * |------------------------------------------------------
         */
        $rooms = HmsRoom::whereHas('roomType', fn($q) => $q->where('business_id', $business_id))
            ->with([
                'roomType.media',
                'roomType.floor',
            ])
            ->get()
            ->map(function ($room) use ($room_bookings) {
                $booking = $room_bookings[$room->id] ?? null;

                /* ---------------- Booking Status ---------------- */
                $room->is_booked = $booking ? 1 : 0;
                $room->is_available = $room->is_booked ? 0 : 1;

                if ($booking) {
                    $room->booking_id = $booking->transaction_id;
                    $is_checked_in = !empty($booking->check_in) && empty($booking->check_out);
                    $room->is_checked_in = $is_checked_in ? 1 : 0;

                    $raw_departure = $booking->hms_booking_departure_date_time ?? null;
                    $departure = $raw_departure ? Carbon::parse($raw_departure) : null;

                    // Timer only runs after check-in. For booked-but-not-checked-in rooms,
                    // pass null so the JS countdown never starts.
                    if ($is_checked_in) {
                        $raw_check_in = $booking->check_in ?? null;
                        $check_in = $raw_check_in ? Carbon::parse($raw_check_in) : null;
                        $room->arrival_at  = $check_in  ? $check_in->format('Y-m-d\TH:i:s')  : null;
                        $room->checkout_at = $departure ? $departure->format('Y-m-d\TH:i:s') : null;
                    } else {
                        $room->arrival_at  = null;
                        $room->checkout_at = null;
                    }
                    $room->time_left_human = $departure && $departure->isFuture()
                        ? $departure->diffForHumans()
                        : null;
                } else {
                    $room->arrival_at   = null;
                    $room->checkout_at  = null;
                    $room->time_left_human = null;
                    $room->is_checked_in = 0;
                }

                /* ---------------- Floor ---------------- */
                $room->floor_name = optional($room->roomType->floor)->name ?? '-';

                /* ---------------- Room Type ---------------- */
                $room->room_type = $room->roomType->type ?? '-';

                /* ---------------- Media Image ---------------- */
                $media = optional($room->roomType)->media->first();

                $room->image_url = $media
                    ? $media->display_url
                    : asset('img/default-room.jpg');

                return $room;
            });
           
        return view('hms::rooms.room_status', compact('rooms'));
    }

    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                'title' => [
                    'text' => $title,
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical',
                'padding' => 20,
            ],
        ];
    }

    public function get_booking_count($date)
    {
        $business_id = request()->session()->get('user.business_id');

        return Transaction::where('transactions.type', 'hms_booking')
            ->where('transactions.business_id', $business_id)
            ->where('status', 'confirmed')
            ->whereDate('transactions.hms_booking_arrival_date_time', '<=', $date)
            ->whereDate('transactions.hms_booking_departure_date_time', '>=', $date)
            ->count();
    }

    public function get_today_arrival_departure_booking($type)
    {
        $today = Carbon::now();
        $business_id = request()->session()->get('user.business_id');

        return HmsTransactionClass::where('transactions.business_id', $business_id)
            ->where('transactions.status', 'confirmed')
            ->with(['contact', 'hms_booking_lines'])
            ->whereDate('transactions.' . $type . '', '=', $today)
            ->where('transactions.type', 'hms_booking')
            ->get();
    }

    public function leave_arrive_count_today($type)
    {
        $today = Carbon::now();
        $business_id = request()->session()->get('user.business_id');

        return HmsBookingLine::join('transactions', 'hms_booking_lines.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'confirmed')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'hms_booking')
            ->whereDate('transactions.' . $type . '', '=', $today)
            ->selectRaw('SUM(hms_booking_lines.adults) as adult_guests, SUM(hms_booking_lines.childrens) as child_guests')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }

        $amenities = Category::where('business_id', $business_id)
            ->where('category_type', 'amenities')
            ->select(['name', 'id'])
            ->get();

        $buildings = HmsBuilding::where('business_id', $business_id)->pluck('name', 'id');

        return view('hms::rooms.create', compact('amenities', 'buildings'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $rooms = array_values(array_filter($request->input('rooms', []), fn($r) => trim((string)$r) !== ''));
            $amenities = $request->input('amenities', []);

            $input = $request->only(['type', 'no_of_adult', 'no_of_child', 'max_occupancy', 'description', 'hms_floor_id']);
            $input['created_by'] = auth()->user()->id;
            $input['business_id'] = $business_id;
            $input['hms_floor_id'] = $request->hms_floor_id ?: null;

            $type = HmsRoomType::create($input);

            Media::uploadMedia($type->business_id, $type, $request, 'images');

            $type->categories()->sync($amenities);

            $room_lines = [];
            foreach ($rooms as $room) {
                $room_lines[] = new HmsRoom([
                    'room_number' => $room,
                ]);
            }

            $type->rooms()->saveMany($room_lines);
            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('hms::lang.room_created_succesfully'),
            ];

            if ($request->input('submit_type') == 'save_and_pricing') {
                return redirect()->action(
                    [\Modules\Hms\Http\Controllers\RoomController::class, 'pricing'],
                    ['room_id' => $type->id]
                );
            }

            return redirect()
                ->action([\Modules\Hms\Http\Controllers\RoomController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('hms::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }
        $room_type = HmsRoomType::where('business_id', $business_id)->with(['rooms', 'media', 'floor.building'])->findOrFail($id);

        $existing_amenities = $room_type->categories->map(function ($category) {
            return $category->id;
        })->all();

        $amenities = Category::where('business_id', $business_id)
            ->where('category_type', 'amenities')
            ->select(['name', 'id'])
            ->get();

        $buildings = HmsBuilding::where('business_id', $business_id)->pluck('name', 'id');

        $current_building_id = optional($room_type->floor)->hms_building_id;

        return view('hms::rooms.edit', compact('room_type', 'amenities', 'existing_amenities', 'buildings', 'current_building_id'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }

        $type = HmsRoomType::where('business_id', $business_id)->findOrFail($id);
        DB::beginTransaction();
        try {
            $input = $request->only(['type', 'no_of_adult', 'no_of_child', 'max_occupancy', 'description', 'hms_floor_id']);
            $input['hms_floor_id'] = $request->hms_floor_id ?: null;

            $type->update($input);

            $rooms = $request->input('rooms', []);

            $type = HmsRoomType::find($id);

            $amenities = $request->amenities ?? [];
            $type->categories()->sync($amenities);

            Media::uploadMedia($type->business_id, $type, $request, 'images');

            $existing_room_ids = $type->rooms->pluck('id')->toArray();

            foreach ($rooms as $room) {
                if (isset($room['id']) && in_array($room['id'], $existing_room_ids)) {
                    // Update the existing room if it exists in the database
                    HmsRoom::where('id', $room['id'])->update(['room_number' => $room['name']]);
                } else {
                    // Create a new room if it doesn't have an ID or doesn't exist in the database
                    $type->rooms()->create(['room_number' => $room['name']]);
                }
            }

            // Delete rooms that are not in the updated list
            $rooms_to_delete = array_diff($existing_room_ids, array_column($rooms, 'id'));

            HmsRoom::whereIn('id', $rooms_to_delete)->delete();

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('hms::lang.room_updated_succesfully'),
            ];

            if ($request->input('submit_type') == 'save_and_pricing') {
                return redirect()->action(
                    [\Modules\Hms\Http\Controllers\RoomController::class, 'pricing'],
                    ['room_id' => $type->id]
                );
            }

            return redirect()
                ->action([\Modules\Hms\Http\Controllers\RoomController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    public function deleteMedia($media_id)
    {
        if (!auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                \App\Media::deleteMedia($business_id, $media_id);
                $output = ['success' => true, 'msg' => __('lang_v1.file_deleted_successfully')];
            } catch (\Exception $e) {
                $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
            }
            return response()->json($output);
        }
    }

    // pricing index page retune
    public function pricing(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.manage_price')) {
            abort(403, 'Unauthorized action.');
        }

        $id = $request->input('room_id');

        $room_type = [];
        $default_pricing = [];
        $spacial_pricing = [];

        if (!empty($id)) {
            $room_type = HmsRoomType::where('business_id', $business_id)->findOrFail($id);

            $default_pricing = HmsRoomTypePricing::where('hms_room_type_id', $id)->whereNull('adults')->whereNull('childrens')->first();

            $spacial_pricing = HmsRoomTypePricing::where('hms_room_type_id', $id)->whereNotNull('adults')->whereNotNull('childrens')->get();
        }

        $types = HmsRoomType::where('business_id', $business_id)->pluck('type', 'id')->toArray();

        return view('hms::rooms.pricing', compact('room_type', 'default_pricing', 'spacial_pricing', 'types'));
    }

    // get htlm for pricing add more pricing
    public function get_spacial_pricing_html(Request $request)
    {
        $currentIndex = $request->input('currentIndex');
        $id = $request->input('id');
        $room_type = HmsRoomType::findOrFail($id);
        return view('hms::rooms.spacial_pricing', compact('currentIndex', 'room_type'));
    }

    // store or update  pricing
    public function post_pricing(Request $request)
    {
        $input = $request->except(['_token']);
        $type = HmsRoomType::findOrFail($input['type_id']);

        try {
            DB::beginTransaction();
            $existing_ids = $type->pricings->pluck('id')->toArray();

            $new_created_id = [];

            foreach ($input['pricing'] as $pricing) {
                if (isset($pricing['id']) && in_array($pricing['id'], $existing_ids)) {
                    // Update the existing pricing if it exists in the database
                    HmsRoomTypePricing::where('id', $pricing['id'])->update(
                        [
                            'season_type' => $input['season_type'],
                            'adults' => $pricing['adults'] ?? null,
                            'childrens' => $pricing['childrens'] ?? null,
                            'default_price_per_night' => $pricing['default_price'] ?? null,
                            'price_monday' => $pricing['monday'] ?? null,
                            'price_tuesday' => $pricing['tuesday'] ?? null,
                            'price_wednesday' => $pricing['wednesday'] ?? null,
                            'price_thursday' => $pricing['thursday'] ?? null,
                            'price_friday' => $pricing['friday'] ?? null,
                            'price_saturday' => $pricing['saturday'] ?? null,
                            'price_sunday' => $pricing['sunday'] ?? null,
                        ]
                    );
                } else {
                    // Create a pricing if it doesn't have an ID or doesn't exist in the database
                    $type_pricing = $type->pricings()->create(
                        [
                            'season_type' => $input['season_type'],
                            'adults' => $pricing['adults'] ?? null,
                            'childrens' => $pricing['childrens'] ?? null,
                            'default_price_per_night' => $pricing['default_price'] ?? null,
                            'price_monday' => $pricing['monday'] ?? null,
                            'price_tuesday' => $pricing['tuesday'] ?? null,
                            'price_wednesday' => $pricing['wednesday'] ?? null,
                            'price_thursday' => $pricing['thursday'] ?? null,
                            'price_friday' => $pricing['friday'] ?? null,
                            'price_saturday' => $pricing['saturday'] ?? null,
                            'price_sunday' => $pricing['sunday'] ?? null,
                        ]
                    );
                    $new_created_id[] = $type_pricing->id;
                }
            }

            // Delete pricing that are not in the updated list
            $pricing_to_delete = array_diff($existing_ids, array_column($input['pricing'], 'id'));
            HmsRoomTypePricing::whereIn('id', $pricing_to_delete)->delete();

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return redirect()
                ->action([\Modules\Hms\Http\Controllers\RoomController::class, 'pricing'], ['room_id' => $input['type_id']])
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.manage_rooms')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $type = HmsRoomType::where('business_id', $business_id)->findOrFail($id);
            $type->delete();

            HmsRoom::where('hms_room_type_id', $id)->delete();

            $output = ['success' => 1, 'msg' => __('lang_v1.success')];
            return redirect()
                ->action([\Modules\Hms\Http\Controllers\RoomController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }
}
