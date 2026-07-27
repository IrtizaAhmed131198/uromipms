<?php

namespace Modules\Hms\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Transaction;
use App\User;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Hms\Entities\HmsBookingLine;
use Modules\Hms\Entities\HmsRoom;
use Modules\Hms\Entities\HmsRoomChangeLog;
use Modules\Hms\Entities\HmsRoomType;
use Modules\Hms\Entities\HmsStayExtension;
use Modules\Hms\Entities\HmsTransactionClass;
use Modules\Hms\Exports\GeneralGuestReportExport;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;


class HmsReportController extends Controller
{
    protected $commonUtil;
    protected $moduleUtil;

    public function __construct(
        Util $commonUtil, ModuleUtil $moduleUtil

    ) {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->date_to && $request->date_from) {

            $business_id = request()->session()->get('user.business_id');

            $date_to= $this->commonUtil->uf_date($request->date_to) . ' 00:00:00';
            $date_from = $this->commonUtil->uf_date($request->date_from) . ' 23:59:59';

            // all booking report
            $total_booking = $this->count_booking_by_status($date_to, $date_from, ['confirmed', 'cancelled', 'pending']);
            // all confirmed booking 
            $total_confirmed_booking =  $this->count_booking_by_status($date_to, $date_from, ['confirmed']);

            //all cancelled booking\\
            $total_cancelled_booking =  $this->count_booking_by_status($date_to, $date_from, ['cancelled']); 

            // all pending booking
            $total_pending_booking =  $this->count_booking_by_status($date_to, $date_from, ['pending']); 
  
            // booking count by room
            $transactions_with_room = Transaction::where('status', 'confirmed')
                ->where('type', 'hms_booking')
                ->where('transactions.business_id', $business_id)
                ->select('transactions.id', DB::raw('COUNT(hms_booking_lines.id) as line_count'))
                ->leftJoin('hms_booking_lines', 'transactions.id', '=', 'hms_booking_lines.transaction_id')
                ->groupBy('transactions.id')
                ->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])
                ->get();

            $rooms_by_booking_count = $this->count_booking_by_room($transactions_with_room);
            
            $count_booking_by_night = Transaction::where('status', 'confirmed')
                ->where('transactions.business_id', $business_id)
                ->where('type', 'hms_booking')
                ->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])
                ->get();

            $count_by_night = $this->count_booking_by_night($count_booking_by_night);

            // booking count by night 
            $transactions_adult_counts = Transaction::where('status', 'confirmed')
            ->where('transactions.business_id', $business_id)
            ->select('transactions.id')
            ->addSelect(DB::raw('SUM(hms_booking_lines.adults) as total_adults'))
            ->leftJoin('hms_booking_lines', 'transactions.id', '=', 'hms_booking_lines.transaction_id')
            ->groupBy('transactions.id')
            ->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])
            ->get();

            $count_by_adults = $this->transactions_adult_counts($transactions_adult_counts);

            // count by room type 
            $pending_room_types = $this->room_type_count('pending', $date_to, $date_from);
            $cancelled_room_types = $this->room_type_count('cancelled', $date_to, $date_from);  
            $confirmed_room_types = $this->room_type_count('confirmed', $date_to, $date_from);
            
            $all_room_types = HmsRoomType::select(
                'hms_room_types.type',
                DB::raw('(SELECT COUNT(DISTINCT transactions.id) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ? ) as transactions_count'),
                DB::raw('(SELECT SUM(hms_booking_lines.total_price) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ? ) as total_price'),
                DB::raw('(SELECT SUM( DATEDIFF(transactions.hms_booking_departure_date_time, transactions.hms_booking_arrival_date_time)) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ? ) as total_days'),
                DB::raw('(SELECT SUM(hms_booking_lines.adults + hms_booking_lines.childrens) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?) as no_of_guest'),
            )
            ->setBindings([$date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from])
            ->where('hms_room_types.business_id', $business_id)
            ->get();

            return view('hms::report.index', compact('total_booking', 'total_confirmed_booking', 'total_cancelled_booking', 'total_pending_booking', 'rooms_by_booking_count', 'count_by_night', 'count_by_adults', 'all_room_types', 'confirmed_room_types', 'cancelled_room_types', 'pending_room_types'));
        }
        return view('hms::report.index');
    }


    public function count_booking_by_status($date_to, $date_from, $status){ 
        // all confirmed booking report
        $business_id = session()->get('user.business_id');

        $bookings = HmsTransactionClass::where('transactions.business_id', $business_id)->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])->where('type', 'hms_booking')->whereIn('status', $status)->get();

        $count = (object) [
            'total_guest' => 0,
            'total_adult_guest' => 0, 
            'total_childs_guest' => 0,
            'total_amount' => 0,
            'total_nights' => 0,
            'total_count' => 0,
        ];

        $count->total_count = count($bookings);

        foreach ($bookings as $booking) {
            $count->total_guest = $count->total_guest + $booking->hms_booking_lines->sum('childrens') + $booking->hms_booking_lines->sum('adults');

            $count->total_adult_guest = $count->total_adult_guest + $booking->hms_booking_lines->sum('adults');

            $count->total_childs_guest = $count->total_childs_guest + $booking->hms_booking_lines->sum('childrens');

            $count->total_amount += $booking->final_total;  

            $start = Carbon::parse($booking->hms_booking_arrival_date_time);
            $end = Carbon::parse($booking->hms_booking_departure_date_time);
            // Calculate the difference in days
            $difference_in_days = $end->diffInDays($start);

            $count->total_nights += $difference_in_days;  
        }

        return $count;
    }

    public function room_type_count($status, $date_to, $date_from){
        $business_id = session()->get('user.business_id');
        
        return HmsRoomType::select(
            'hms_room_types.type',
            DB::raw('(SELECT COUNT(DISTINCT transactions.id) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as transactions_count'),
            DB::raw('(SELECT SUM(hms_booking_lines.total_price) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as total_price'),
            DB::raw('(SELECT SUM( DATEDIFF(transactions.hms_booking_departure_date_time, transactions.hms_booking_arrival_date_time)) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as total_days'),
            DB::raw('(SELECT SUM(hms_booking_lines.adults + hms_booking_lines.childrens) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as no_of_guest'),
        )
        ->setBindings([$date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from])
        ->where('hms_room_types.business_id', $business_id)
        ->get();
    }

    public function transactions_adult_counts($transactions){
        $count = (object) [
            'one_adult_count' => 0,
            'two_adults_count' => 0,
            'three_adults_count' => 0,
            'four_adults_count' => 0,
            'five_adults_count' => 0,
            'six_adults_count' => 0,
            'more_than_six_adults_count' => 0,
        ];
        
        foreach ($transactions as $transaction) {
            $totalAdults = $transaction->total_adults;
            switch ($totalAdults) {
                case 1:
                    $count->one_adult_count++;
                    break;
                case 2:
                    $count->two_adults_count++;
                    break;
                case 3:
                    $count->three_adults_count++;
                    break;
                case 4:
                    $count->four_adults_count++;
                    break;
                case 5:
                    $count->five_adults_count++;
                    break;
                case 6:
                    $count->six_adults_count++;
                    break;
                default:
                    $count->more_than_six_adults_count++;
                    break;
            }
        }

        return $count;
    }

    public function count_booking_by_night($bookings){
        $counts = (object) [
            'one_night_count' => 0,
            'two_night_count' => 0,
            'three_night_count' => 0,
            'four_night_count' => 0,
            'five_night_count' => 0,
            'six_night_count' => 0,
            'more_than_six_night_count' => 0,
        ];
        
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->hms_booking_arrival_date_time);
            $end = Carbon::parse($booking->hms_booking_departure_date_time);
            // Calculate the difference in days
            $nights = $end->diffInDays($start);

            // echo $nights;

            switch ($nights) {
                case 0:
                    $counts->one_night_count++;
                    break;
                case 1:
                    $counts->one_night_count++;
                    break;
                case 2:
                    $counts->two_night_count++;
                    break;
                case 3:
                    $counts->three_night_count++;
                    break;
                case 4:
                    $counts->four_night_count++;
                    break;
                case 5:
                    $counts->five_night_count++;
                    break;
                case 6:
                    $counts->six_night_count++;
                    break;
                default:
                    if ($nights > 6) {
                        $counts->more_than_six_night_count++;
                    }
            }
        }  
        
        return $counts;
    }

    public function count_booking_by_room($transactions){
        
        $lineCounts = (object) [
            'one_line_count' => 0,
            'two_lines_count' => 0,
            'more_than_two_lines_count' => 0,
        ];
        
        foreach ($transactions as $transaction) {
            $lineCount = $transaction->line_count;
        
            switch ($lineCount) {
                case 1:
                    $lineCounts->one_line_count++;
                    break;
                case 2:
                    $lineCounts->two_lines_count++;
                    break;
                default:
                    $lineCounts->more_than_two_lines_count++;
                    break;
            }
        }

        return $lineCounts;
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hms::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
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
        return view('hms::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function general_guest_report(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $rooms = HmsRoom::leftJoin('hms_room_types as rt', 'rt.id', '=', 'hms_rooms.hms_room_type_id')
            ->where('rt.business_id', $business_id)
            ->select('hms_rooms.id', 'hms_rooms.room_number')
            ->pluck('room_number', 'id')
            ->toArray();

        $users = User::forDropdown($business_id, false);

        $statuses = [
            'pending'   => __('hms::lang.pending'),
            'confirmed' => __('hms::lang.confirmed'),
            'cancelled' => __('hms::lang.cancelled'),
        ];

        return view('hms::report.general_guest_report', compact('rooms', 'users', 'statuses'));
    }

    public function general_guest_data(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $query = $this->buildGuestReportQuery($business_id, $request);

        return DataTables::of($query)
            ->editColumn('hms_booking_arrival_date_time', function ($row) {
                return $row->hms_booking_arrival_date_time
                    ? $this->commonUtil->format_date($row->hms_booking_arrival_date_time, true)
                    : '';
            })
            ->editColumn('hms_booking_departure_date_time', function ($row) {
                return $row->hms_booking_departure_date_time
                    ? $this->commonUtil->format_date($row->hms_booking_departure_date_time, true)
                    : '';
            })
            ->editColumn('check_in', function ($row) {
                return $row->check_in
                    ? $this->commonUtil->format_date($row->check_in, true)
                    : '';
            })
            ->editColumn('check_out', function ($row) {
                return $row->check_out
                    ? $this->commonUtil->format_date($row->check_out, true)
                    : '';
            })
            ->editColumn('changed_at', function ($row) {
                return $row->changed_at
                    ? $this->commonUtil->format_date($row->changed_at, true)
                    : '';
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'confirmed') {
                    return '<span class="badge bg-green">' . ucfirst($row->status) . '</span>';
                } elseif ($row->status == 'pending') {
                    return '<span class="badge bg-yellow">' . ucfirst($row->status) . '</span>';
                } elseif ($row->status == 'cancelled') {
                    return '<span class="badge bg-red">' . ucfirst($row->status) . '</span>';
                }
                return ucfirst($row->status ?? '');
            })
            ->editColumn('final_total', function ($row) {
                return '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->final_total . '">' . $this->commonUtil->num_f($row->final_total, true) . '</span>';
            })
            ->editColumn('total_paid', function ($row) {
                return '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->total_paid . '">' . $this->commonUtil->num_f($row->total_paid, true) . '</span>';
            })
            ->editColumn('balance', function ($row) {
                return '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->balance . '">' . $this->commonUtil->num_f($row->balance, true) . '</span>';
            })
            ->editColumn('ext_charges', function ($row) {
                return $row->ext_charges
                    ? '<span class="display_currency" data-currency_symbol="true">' . $this->commonUtil->num_f($row->ext_charges, true) . '</span>'
                    : '-';
            })
            ->editColumn('hbl_total_price', function ($row) {
                return '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . ($row->hbl_total_price ?? 0) . '">' . $this->commonUtil->num_f($row->hbl_total_price ?? 0, true) . '</span>';
            })
            ->rawColumns(['status', 'final_total', 'total_paid', 'balance', 'ext_charges', 'hbl_total_price'])
            ->make(true);
    }

    public function general_guest_pdf(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $bookings = $this->buildGuestReportQuery($business_id, $request)->get();
        $business = \App\Business::find($business_id);

        $html = view('hms::report.pdf.general_guest_report_pdf', compact('bookings', 'business', 'request'))->render();

        $mpdf = new \Mpdf\Mpdf([
            'tempDir'          => public_path('uploads/temp'),
            'mode'             => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
            'margin_top'       => 8,
            'margin_bottom'    => 8,
            'format'           => 'A4-L',
        ]);
        $mpdf->useSubstitutions = true;
        $mpdf->SetTitle('General Guest Report');
        $mpdf->WriteHTML($html);
        $mpdf->Output('general_guest_report.pdf', 'D');
    }

    public function general_guest_excel(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        return Excel::download(
            new GeneralGuestReportExport($request->all(), $business_id),
            'general_guest_report.xlsx'
        );
    }

    private function buildGuestReportQuery(int $business_id, $request)
    {
        $query = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'hms_booking')
            ->leftJoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->leftJoin('hms_booking_lines as hbl', 'hbl.transaction_id', '=', 'transactions.id')
            ->leftJoin('hms_rooms as room', 'room.id', '=', 'hbl.hms_room_id')
            ->leftJoin('hms_room_types as rtype', 'rtype.id', '=', 'hbl.hms_room_type_id')
            ->leftJoin(\DB::raw('(SELECT transaction_id,
                SUM(IF(is_return=0,amount,-amount)) as total_paid,
                GROUP_CONCAT(DISTINCT method SEPARATOR ", ") as pay_methods
                FROM transaction_payments GROUP BY transaction_id) as tp'),
                'tp.transaction_id', '=', 'transactions.id')
            ->leftJoin(\DB::raw('(SELECT transaction_id,
                SUM(additional_charges) as ext_charges,
                MAX(new_departure_datetime) as new_departure,
                MIN(old_departure_datetime) as orig_departure,
                SUM(extra_nights) as ext_nights
                FROM hms_stay_extensions GROUP BY transaction_id) as se'),
                'se.transaction_id', '=', 'transactions.id')
            ->leftJoin(\DB::raw('(SELECT transaction_id,
                from_room_id, to_room_id, created_at as changed_at, note as change_note
                FROM hms_room_change_logs GROUP BY transaction_id) as hrcl'),
                'hrcl.transaction_id', '=', 'transactions.id')
            ->leftJoin('hms_rooms as from_room', 'from_room.id', '=', 'hrcl.from_room_id')
            ->leftJoin('hms_rooms as to_room', 'to_room.id', '=', 'hrcl.to_room_id')
            ->select([
                'transactions.id', 'transactions.ref_no', 'transactions.status',
                'transactions.hms_booking_arrival_date_time',
                'transactions.hms_booking_departure_date_time',
                'transactions.check_in', 'transactions.check_out',
                'transactions.final_total',
                \DB::raw('COALESCE(NULLIF(c.name,""), c.supplier_business_name) as guest_name'),
                'c.mobile', 'c.contact_id as id_number',
                \DB::raw('CONCAT_WS(", ", NULLIF(c.city,""), NULLIF(c.state,""), NULLIF(c.country,"")) as address'),
                'room.room_number', 'rtype.type as room_type', 'hbl.price as room_rate',
                'hbl.total_price as hbl_total_price',
                \DB::raw('(hbl.adults + hbl.childrens) as total_guests'),
                \DB::raw('CONCAT(u.first_name," ",IFNULL(u.last_name,"")) as staff_name'),
                \DB::raw('COALESCE(tp.total_paid, 0) as total_paid'), 'tp.pay_methods',
                \DB::raw('(transactions.final_total - COALESCE(tp.total_paid,0)) as balance'),
                'se.ext_charges', 'se.ext_nights',
                'se.new_departure', 'se.orig_departure',
                'from_room.room_number as from_room_number', 'to_room.room_number as to_room_number',
                'hrcl.changed_at', 'hrcl.change_note',
            ])
            ->groupBy('transactions.id');

        if (!empty($request->date_from)) {
            $date_from = $this->commonUtil->uf_date($request->date_from) . ' 00:00:00';
            $query->where('transactions.hms_booking_arrival_date_time', '>=', $date_from);
        }
        if (!empty($request->date_to)) {
            $date_to = $this->commonUtil->uf_date($request->date_to) . ' 23:59:59';
            $query->where('transactions.hms_booking_arrival_date_time', '<=', $date_to);
        }
        if (!empty($request->room_id)) {
            $query->where('room.id', $request->room_id);
        }
        if (!empty($request->guest_name)) {
            $query->where(\DB::raw('COALESCE(NULLIF(c.name,""), c.supplier_business_name)'), 'like', '%' . $request->guest_name . '%');
        }
        if (!empty($request->staff_id)) {
            $query->where('transactions.created_by', $request->staff_id);
        }
        if (!empty($request->status)) {
            $query->where('transactions.status', $request->status);
        }

        $tab = $request->input('tab_type', 'general');
        if ($tab === 'daily') {
            $query->whereDate('transactions.hms_booking_arrival_date_time', now()->toDateString());
        } elseif ($tab === 'checked_in') {
            $query->whereNotNull('transactions.check_in')->whereNull('transactions.check_out');
        } elseif ($tab === 'checkout') {
            $query->whereNotNull('transactions.check_out');
        } elseif ($tab === 'extension') {
            $query->whereNotNull('se.ext_charges');
        } elseif ($tab === 'room_change') {
            $query->whereNotNull('from_room.room_number');
        }

        return $query;
    }
}
  