<?php

namespace Modules\Hms\Http\Controllers;

use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\Account;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\NotificationTemplate;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Hms\Entities\HmsBookingExtra;
use Modules\Hms\Entities\HmsBookingLine;
use Modules\Hms\Entities\HmsExtra;
use Modules\Hms\Entities\HmsRoom;
use App\TransactionPayment;
use Modules\Hms\Entities\HmsRoomChangeLog;
use Modules\Hms\Entities\HmsStayExtension;
use Modules\Hms\Entities\HmsRoomType;
use Modules\Hms\Entities\HmsRoomTypePricing;
use Modules\Hms\Entities\HmsTransactionClass;
use Modules\Hms\Notifications\CustomerNotification;
use Yajra\DataTables\Facades\DataTables;
use Notification;

class HmsBookingController extends Controller
{
    protected $commonUtil;
    protected $notificationUtil;
    protected $contactUtil;
    protected $transactionUtil;
    protected $dummyPaymentLine;
    protected $productUtil;
    protected $businessUtil;

    public function __construct(
        Util $commonUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
    ) {
        $this->commonUtil = $commonUtil;
        $this->notificationUtil = $notificationUtil;
        $this->contactUtil = $contactUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
        ];
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

            $booking = Transaction::where('transactions.business_id', $business_id)
                ->with(['payment_lines'])
                ->leftjoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
                ->where('transactions.type', 'hms_booking')
                ->select('transactions.*', 'c.name as c_name', DB::raw('COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id), 0) as total_paid'));

            // filter with contact
            if ($request->customer_id) {
                $booking = $booking->where('c.id', $request->customer_id);
            }
            // filter with status
            if ($request->status) {
                $booking = $booking->where('transactions.status', $request->status);
            }

            // filtter with status
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $booking->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $booking
                    ->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            return Datatables::of($booking)
                ->editColumn('created_at', '{{ @format_datetime($created_at) }}')
                ->addColumn('action', function ($row) use ($business_id) {
                    $html = '';

                    // Delete booking — superadmin only, or admin if superadmin module not installed
                    $superadmin_installed = $this->moduleUtil->isSuperadminInstalled();
                    $can_delete = auth()->user()->can('superadmin') ||
                        (!$superadmin_installed && $this->productUtil->is_admin(auth()->user(), $business_id));

                    if ($can_delete) {
                        $html .= '<button type="button"
                class="tw-dw-btn tw-dw-btn-error tw-dw-btn-outline tw-dw-btn-xs btn-delete-booking"
                data-id="' . $row->id . '"
                data-url="' . action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'destroy'], $row->id) . '"
                style="margin:4px">'
                            . __('messages.delete')
                            . '</button>';
                    }

                    // Edit booking
                    if (auth()->user()->can('hms.edit_booking')) {
                        $html .= '<a type="button"
                class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-outline tw-dw-btn-xs btn-modal-extra"
                href="' . action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'edit'], ['booking' => $row->id]) . '"
                style="margin:4px">'
                            . __('hms::lang.edit_booking')
                            . '</a>';
                    }

                    // Receipt generate
                    if (auth()->user()->can('hms.reciept_booking')) {
                        $html .= '<a type="button"
    class="tw-dw-btn tw-dw-btn-warning tw-dw-btn-outline tw-dw-btn-xs js-generate-receipt"
    data-id="' . $row->id . '"
    href="' . route('hms.booking.receipt', ['id' => $row->id]) . '"
    style="margin:4px">'
                            . __('hms::lang.reciept_booking')
                            . '</a>';
                    }

                    // Check-in / Check-out
                    if ($row->status == 'confirmed') {
                        if (empty($row->check_in)) {
                            $html .= '<a type="button"
                    class="tw-dw-btn tw-dw-btn-info tw-dw-btn-outline tw-dw-btn-xs btn-modal-checkIn"
                    href="' . action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_check_in_out'], ['id' => $row->id]) . '"
                    style="margin:4px">'
                                . __('hms::lang.check_in')
                                . '</a>';
                        } elseif (!empty($row->check_in) && empty($row->check_out)) {
                            $html .= '<a type="button"
                    class="tw-dw-btn tw-dw-btn-error tw-dw-btn-outline tw-dw-btn-xs btn-modal-checkIn"
                    href="' . action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_check_in_out'], ['id' => $row->id]) . '"
                    style="margin:4px">'
                                . __('hms::lang.check_out')
                                . '</a>';
                            $html .= '<a type="button"
                    class="tw-dw-btn tw-dw-btn-warning tw-dw-btn-outline tw-dw-btn-xs btn-modal-change-room"
                    href="' . route('hms.booking.get_change_room', ['id' => $row->id]) . '"
                    style="margin:4px">'
                                . __('hms::lang.change_room')
                                . '</a>';
                            // Extend Stay button (same condition as Change Room)
                            if (auth()->user()->can('hms.extend_stay')) {
                                $html .= '<a type="button"
                    class="tw-dw-btn tw-dw-btn-accent tw-dw-btn-outline tw-dw-btn-xs btn-extend-stay"
                    href="' . route('hms.booking.get_extend_stay', ['id' => $row->id]) . '"
                    style="margin:4px">'
                                    . __('hms::lang.extend_stay')
                                    . '</a>';
                            }
                        }
                    }

                    // View booking
                    $html .= '<a type="button"
            class="tw-dw-btn tw-dw-btn-success tw-dw-btn-outline tw-dw-btn-xs btn-modal-extra"
            href="' . action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'show'], ['booking' => $row->id]) . '"
            style="margin:4px">'
                        . __('hms::lang.view')
                        . '</a>';

                    // Cancel booking (only if not already cancelled or checked-out)
                    if (auth()->user()->can('hms.cancel_booking') && $row->status != 'cancelled' && empty($row->check_out)) {
                        $html .= '<button type="button"
                class="tw-dw-btn tw-dw-btn-warning tw-dw-btn-outline tw-dw-btn-xs btn-cancel-booking"
                data-id="' . $row->id . '"
                style="margin:4px">'
                            . __('hms::lang.cancel_booking')
                            . '</button>';
                    }

                    return $html;
                })
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sell.partials.payment_status', [
                            'payment_status' => $payment_status,
                            'id' => $row->id
                        ]);
                    }
                )
                ->editColumn(
                    'stay',
                    '{{ @format_datetime($hms_booking_arrival_date_time) }} - {{ @format_datetime($hms_booking_departure_date_time) }}'
                )
                ->editColumn('status', function ($row) {
                    if ($row->status == 'confirmed') {
                        if (!empty($row->check_in) && empty($row->check_out)) {
                            return '<h6 class="badge bg-green">' . ucfirst($row->status) . '</h6><br>
                        <h6 class="badge bg-info">' . __('hms::lang.check_in') . ' '
                                . $this->commonUtil->format_date($row->check_in, true) . '</h6>';
                        } elseif (!empty($row->check_in) && !empty($row->check_out)) {
                            return '<h6 class="badge bg-green">' . ucfirst($row->status) . '</h6><br>
                        <h6 class="badge bg-info">' . __('hms::lang.check_in') . ' '
                                . $this->commonUtil->format_date($row->check_in, true) . '</h6><br>
                        <h6 class="badge bg-red">' . __('hms::lang.check_out') . ' '
                                . $this->commonUtil->format_date($row->check_out, true) . '</h6>';
                        }

                        return '<h6 class="badge bg-green">' . ucfirst($row->status) . '</h6>';
                    } elseif ($row->status == 'pending') {
                        return '<h6 class="badge bg-yellow">' . ucfirst($row->status) . '</h6>';
                    } elseif ($row->status == 'cancelled') {
                        return '<h6 class="badge bg-red">' . ucfirst($row->status) . '</h6>';
                    }
                })
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';

                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]] ?? '';
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    return !empty($payment_method)
                        ? '<span class="payment-method" data-orig-value="' . $payment_method . '">' . $payment_method . '</span>'
                        : '';
                })
                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn(
                    'total_paid',
                    '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $remaining = $row->final_total - ($row->total_paid ?? 0);
                    return '<span class="payment_due" data-orig-value="' . $remaining . '">'
                        . $this->transactionUtil->num_f($remaining, true)
                        . '</span>';
                })
                ->rawColumns([
                    'created_at',
                    'action',
                    'stay',
                    'status',
                    'payment_status',
                    'payment_methods',
                    'final_total',
                    'total_paid',
                    'total_remaining'
                ])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id, false);
        $status = [
            'pending' => __('hms::lang.pending'),
            'confirmed' => __('hms::lang.confirmed'),
            'cancelled' => __('hms::lang.cancelled'),
        ];
        return view('hms::bookings.index', compact('customers', 'status'));
    }

    public function generateReceipt($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $transaction = HmsTransactionClass::where('transactions.business_id', $business_id)
            ->with(['contact'])
            ->leftJoin('hms_booking_lines as hbl', 'transactions.id', '=', 'hbl.transaction_id')
            ->leftJoin('hms_booking_extras as hbe', 'transactions.id', '=', 'hbe.transaction_id')
            ->leftJoin('hms_coupons as coupons', 'transactions.hms_coupon_id', '=', 'coupons.id')
            ->where('transactions.type', 'hms_booking')
            ->select(
                'transactions.*',
                DB::raw('(SELECT SUM(total_price) FROM hms_booking_lines WHERE transaction_id = transactions.id) as room_price'),
                DB::raw('(SELECT SUM(price) FROM hms_booking_extras WHERE transaction_id = transactions.id) as extra_price'),
                'coupons.coupon_code'
            )
            ->groupBy('transactions.id')
            ->findOrFail($id);

        $booking_rooms = HmsBookingLine::where('hms_booking_lines.transaction_id', $id)
            ->leftJoin('hms_rooms as room', 'room.id', '=', 'hms_booking_lines.hms_room_id')
            ->leftJoin('hms_room_types as type', 'type.id', '=', 'hms_booking_lines.hms_room_type_id')
            ->select(
                'hms_booking_lines.*',
                'room.room_number',
                'type.type as room_type_name',
            )
            ->get();

        $business = Business::find($business_id);
        $location = BusinessLocation::where('business_id', $business_id)->first();
        $invoice_layout = $location && $location->invoice_layout_id
            ? \App\InvoiceLayout::find($location->invoice_layout_id)
            : \App\InvoiceLayout::where('business_id', $business_id)->where('is_default', 1)->first();

        $html = view('hms::bookings.pos_receipt', compact('transaction', 'booking_rooms', 'business', 'location', 'invoice_layout'))->render();

        if (request()->ajax() || request()->boolean('ajax')) {
            return response()->json(['html' => $html]);
        }

        return $html;
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

        if (!auth()->user()->can('hms.add_booking')) {
            abort(403, 'Unauthorized action.');
        }

        $status = [
            'pending' => __('hms::lang.pending'),
            'confirmed' => __('hms::lang.confirmed'),
            'cancelled' => __('hms::lang.cancelled'),
        ];

        $extras = HmsExtra::where('business_id', $business_id)->get();

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);

        $payment_line = $this->dummyPaymentLine;

        $change_return = $this->dummyPaymentLine;

        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        $business_details = $this->businessUtil->getDetails($business_id);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('hms::bookings.create', compact('status', 'extras', 'walk_in_customer', 'types', 'customer_groups', 'payment_line', 'payment_types', 'pos_settings', 'change_return', 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // return $request;
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.add_booking')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $arrival_date_time = $this->commonUtil->uf_date($request->arrival_date) . ' ' . $this->commonUtil->uf_time($request->arrival_time);
            $departure_date_time = $this->commonUtil->uf_date($request->departure_date) . ' ' . $this->commonUtil->uf_time($request->departure_time);

            $business_id = request()->session()->get('user.business_id');

            $busines = Business::findOrFail($business_id);

            $prefix = json_decode($busines->hms_settings)->prefix ?? null;

            $ref_no = null;

            $ref_count = $this->commonUtil->setAndGetReferenceCount('hms_booking', $business_id);
            // Generate reference number
            $ref_no = $this->commonUtil->generateReferenceNumber('hms_booking', $ref_count, $business_id, $prefix);

            // store in transsaction discount_amount
            $transaction = new HmsTransactionClass();
            $transaction->business_id = $business_id;
            $transaction->type = 'hms_booking';
            // Force 'confirmed' when explicitly checking-in from create page
            $status = $request->input('submit_action') === 'check_in' ? 'confirmed' : $request->status;
            // Also auto-confirm if arrival date is today (time ignored)
            $arrival_is_today = $this->commonUtil->uf_date($request->arrival_date) == Carbon::today()->format('Y-m-d');
            if ($arrival_is_today) {
                $status = 'confirmed';
            }
            $transaction->status = $status;
            $transaction->contact_id = $request->contact_id;
            $transaction->created_by = auth()->user()->id;
            $transaction->ref_no = $ref_no;
            $transaction->total_before_tax = (is_null($request->total_booking_amount) ? 0 : $request->total_booking_amount) + (is_null($request->total_discount) ? 0 : $request->total_discount);
            $transaction->final_total = is_null($request->total_booking_amount) ? 0 : $request->total_booking_amount;

            $transaction->tax_amount = is_null($request->total_discount) ? 0 : $request->total_discount;

            $transaction->discount_amount = is_null($request->total_discount) ? 0 : $request->total_discount;

            $transaction->hms_coupon_id = $request->coupon_id;
            $transaction->discount_type = $request->discount_type;

            $transaction->hms_booking_arrival_date_time = $arrival_date_time;
            $transaction->hms_booking_departure_date_time = $departure_date_time;
            $transaction->save();

            $adults = 0;
            $childrens = 0;
            // store in booking room
            $rooms = $request->rooms ?? [];
            $room_lines = [];
            foreach ($rooms as $room) {
                $room_lines[] = new HmsBookingLine([
                    'hms_room_id' => $room['room_id'],
                    'hms_room_type_id' => $room['type_id'],
                    'adults' => $room['no_of_adult'],
                    'childrens' => $room['no_of_child'],
                    'price' => $room['price'],
                    'total_price' => $room['total_price'],
                ]);
                $adults = $adults + $room['no_of_adult'];
                $childrens = $childrens + $room['no_of_child'];
            }
            $transaction->hms_booking_lines()->saveMany($room_lines);

            // store in booking room
            $extras = $request->extras ?? [];

            $extra_lines = [];
            foreach ($extras as $extra) {
                if (isset($extra['id'])) {
                    $extra_lines[] = new HmsBookingExtra([
                        'hms_extra_id' => $extra['id'],
                        'price' => $extra['price'],
                    ]);
                }
            }
            $transaction->hms_booking_extras()->saveMany($extra_lines);

            // Add change return
            $input = $request->except('_token');
            // Add change return
            $change_return = $this->dummyPaymentLine;
            if (!empty($input['payment']['change_return'])) {
                $change_return = $input['payment']['change_return'];
                unset($input['payment']['change_return']);
            }

            $change_return['amount'] = $input['change_return'] ?? 0;
            $change_return['is_return'] = 1;

            $input['payment'][] = $change_return;

            if (!empty($input['payment'])) {
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
            }

            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            // send notification to customer
            $template = NotificationTemplate::where('template_for', 'hms_new_booking')->where('business_id', $business_id)->first();

            if ($template && $template->auto_send) {
                $data = [
                    'email_body' => $template->email_body,
                    'subject' => $template->subject,
                ];

                $customer = Contact::findOrFail($transaction->contact_id);

                $tag_replaced_data = $this->notificationUtil->replaceHmsBookingTags($data, $transaction, $adults, $childrens, $customer);

                $orig_data = [
                    'email_body' => $tag_replaced_data['email_body'],
                    'subject' => $tag_replaced_data['subject'],
                    'cc' => $template->cc,
                    'bcc' => $template->cc,
                ];

                Notification::route('mail', $customer->email)->notify(new CustomerNotification($orig_data));
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            if ($request->input('submit_action') === 'check_in') {
                return redirect()
                    ->action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index'])
                    ->with('status', $output)
                    ->with('auto_checkin_booking_id', $transaction->id);
            }

            return redirect()
                ->action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index'])
                ->with('status', $output)
                ->with('print_receipt_booking_id', $transaction->id);
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
        $business_id = request()->session()->get('user.business_id');

        $transaction = HmsTransactionClass::where('transactions.business_id', $business_id)
            ->with(['contact'])
            ->leftJoin('hms_booking_lines as hbl', 'transactions.id', '=', 'hbl.transaction_id')
            ->leftJoin('hms_booking_extras as hbe', 'transactions.id', '=', 'hbe.transaction_id')
            ->leftJoin('hms_coupons as coupons', 'transactions.hms_coupon_id', '=', 'coupons.id')
            ->where('transactions.type', 'hms_booking')
            ->select(
                'transactions.*',
                DB::raw('(SELECT SUM(total_price) FROM hms_booking_lines WHERE transaction_id = transactions.id) as room_price'),
                DB::raw('(SELECT SUM(price) FROM hms_booking_extras WHERE transaction_id = transactions.id) as extra_price'),
                'coupons.coupon_code'
            )
            ->groupBy('transactions.id')  // Group by transaction ID
            ->findOrFail($id);

        $extras_id = HmsBookingExtra::where('transaction_id', $id)->pluck('hms_extra_id')->toArray();

        $booking_rooms = HmsBookingLine::where('transaction_id', $id)
            ->leftjoin('hms_rooms as room', 'room.id', '=', 'hms_booking_lines.hms_room_id')
            ->leftjoin('hms_room_types as type', 'type.id', '=', 'hms_booking_lines.hms_room_type_id')
            ->get();

        $extras = HmsExtra::where('business_id', $business_id)->get();

        return view('hms::bookings.show', compact('extras', 'transaction', 'extras_id', 'booking_rooms'));
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

        if (!auth()->user()->can('hms.edit_booking')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $transaction = Transaction::where('transactions.business_id', $business_id)
            ->leftjoin('hms_coupons as coupon', 'transactions.hms_coupon_id', '=', 'coupon.id')
            ->select(['transactions.*', 'coupon.coupon_code'])
            ->findOrFail($id);
        $status = [
            'pending' => __('hms::lang.pending'),
            'confirmed' => __('hms::lang.confirmed'),
            'cancelled' => __('hms::lang.cancelled'),
        ];

        $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);

        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

        $extras_id = HmsBookingExtra::where('transaction_id', $id)->pluck('hms_extra_id')->toArray();

        $booking_rooms = HmsBookingLine::where('transaction_id', $id)
            ->leftjoin('hms_rooms as room', 'room.id', '=', 'hms_booking_lines.hms_room_id')
            ->leftjoin('hms_room_types as type', 'type.id', '=', 'hms_booking_lines.hms_room_type_id')
            ->get();

        $business_id = request()->session()->get('user.business_id');

        $extras = HmsExtra::where('business_id', $business_id)->get();

        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        $business_details = $this->businessUtil->getDetails($business_id);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        // If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $change_return = $this->dummyPaymentLine;

        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('hms::bookings.edit', compact('status', 'extras', 'transaction', 'extras_id', 'booking_rooms', 'types', 'customer_groups', 'customer_due', 'payment_types', 'pos_settings', 'payment_lines', 'change_return', 'accounts'));
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

        if (!auth()->user()->can('hms.edit_booking')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $arrival_date_time = $this->commonUtil->uf_date($request->arrival_date) . ' ' . $this->commonUtil->uf_time($request->arrival_time);
            $departure_date_time = $this->commonUtil->uf_date($request->departure_date) . ' ' . $this->commonUtil->uf_time($request->departure_time);

            $business_id = request()->session()->get('user.business_id');

            // store in transsaction
            $transaction = HmsTransactionClass::findOrFail($id);

            // Force 'confirmed' on edit if arrival date is today
            $status = $request->status;
            $arrival_is_today = $this->commonUtil->uf_date($request->arrival_date) == Carbon::today()->format('Y-m-d');
            if ($arrival_is_today) {
                $status = 'confirmed';
            }
            $transaction->status = $status;
            $transaction->status = $request->status;
            $transaction->contact_id = $request->contact_id;

            $transaction->total_before_tax = (is_null($request->total_booking_amount) ? 0 : $request->total_booking_amount) + (is_null($request->total_discount) ? 0 : $request->total_discount);

            $transaction->tax_amount = is_null($request->total_discount) ? 0 : $request->total_discount;

            $transaction->final_total = is_null($request->total_booking_amount) ? 0 : $request->total_booking_amount;

            $transaction->discount_amount = is_null($request->total_discount) ? 0 : $request->total_discount;
            $transaction->hms_coupon_id = $request->coupon_id;
            $transaction->discount_type = $request->discount_type;

            $transaction->hms_booking_arrival_date_time = $arrival_date_time;
            $transaction->hms_booking_departure_date_time = $departure_date_time;
            $transaction->update();

            HmsBookingLine::where('transaction_id', $id)->delete();
            // store in booking room

            $rooms = $request->rooms ?? [];
            $room_lines = [];
            foreach ($rooms as $room) {
                $room_lines[] = new HmsBookingLine([
                    'hms_room_id' => $room['room_id'],
                    'hms_room_type_id' => $room['type_id'],
                    'adults' => $room['no_of_adult'],
                    'childrens' => $room['no_of_child'],
                    'price' => $room['price'],
                    'total_price' => $room['total_price'],
                ]);
            }
            $transaction->hms_booking_lines()->saveMany($room_lines);

            HmsBookingExtra::where('transaction_id', $id)->delete();
            // store in HmsBookingExtra
            $extras = $request->extras ?? [];
            $extra_lines = [];
            foreach ($extras as $extra) {
                if (isset($extra['id'])) {
                    $extra_lines[] = new HmsBookingExtra([
                        'hms_extra_id' => $extra['id'],
                        'price' => $extra['price'],
                    ]);
                }
            }
            $transaction->hms_booking_extras()->saveMany($extra_lines);

            // Add change return
            $input = $request->except('_token');
            $change_return = $this->dummyPaymentLine;
            if (!empty($input['payment']['change_return'])) {
                $change_return = $input['payment']['change_return'];
                unset($input['payment']['change_return']);
            }

            // Add change return
            $change_return['amount'] = $input['change_return'] ?? 0;
            $change_return['is_return'] = 1;
            if (!empty($input['change_return_id'])) {
                $change_return['payment_id'] = $input['change_return_id'];
            }
            $input['payment'][] = $change_return;

            if (!empty($input['payment'])) {
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
            }

            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return redirect()
                ->action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index'])
                ->with('status', $output)
                ->with('print_receipt_booking_id', $transaction->id);
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
     * Cancel a booking (sets status to cancelled).
     */
    public function cancel($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.cancel_booking')) {
            return response()->json(['success' => 0, 'msg' => __('lang_v1.unauthorized')]);
        }

        try {
            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'hms_booking')
                ->findOrFail($id);

            if ($transaction->status === 'cancelled') {
                return response()->json(['success' => 0, 'msg' => __('hms::lang.already_cancelled')]);
            }

            $transaction->status = 'cancelled';
            $transaction->save();

            return response()->json(['success' => 1, 'msg' => __('hms::lang.booking_cancelled_successfully')]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    /**
     * Delete a booking and all related data.
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.delete_booking')) {
            return response()->json(['success' => 0, 'msg' => __('lang_v1.unauthorized')]);
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'hms_booking')
                ->findOrFail($id);

            // Delete related records
            HmsBookingLine::where('transaction_id', $id)->delete();
            HmsBookingExtra::where('transaction_id', $id)->delete();
            \Modules\Hms\Entities\HmsRoomChangeLog::where('transaction_id', $id)->delete();

            // Delete payments
            TransactionPayment::where('transaction_id', $id)->delete();

            $transaction->delete();

            DB::commit();

            return response()->json(['success' => 1, 'msg' => __('hms::lang.booking_deleted_successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    // this function return modal for add room during booking
    public function booking_room_add()
    {
        $business_id = request()->session()->get('user.business_id');

        $types = HmsRoomType::where('business_id', $business_id)->whereRaw('EXISTS (SELECT 1 FROM hms_room_type_pricings WHERE hms_room_type_id = hms_room_types.id)')->pluck('type', 'id')->toArray();

        return view('hms::bookings.add_room', compact('types'));
    }

    // this function return modal for edit singal room during booking
    public function booking_room_edit(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $no_of_child = $request->input('no_of_child');
        $no_of_adult = $request->input('no_of_adult');
        $room_id = $request->input('room_id');
        $type_id = $request->input('type_id');

        $type = HmsRoomType::find($type_id);

        $types = HmsRoomType::where('business_id', $business_id)->whereRaw('EXISTS (SELECT 1 FROM hms_room_type_pricings WHERE hms_room_type_id = hms_room_types.id)')->pluck('type', 'id')->toArray();

        $room = HmsRoom::find($request->input('room_id'));

        $existing_rooms = [];

        if (!empty($request->input('room_ids'))) {
            $existing_rooms = $request->input('room_ids');
            $existing_rooms = array_diff($existing_rooms, [$room_id]);
        }

        $rooms = HmsRoom::where('hms_room_type_id', $type_id)->whereNotIn('id', $existing_rooms)->pluck('room_number', 'id')->toArray();

        return view('hms::bookings.edit_room', compact('types', 'type', 'rooms', 'room_id', 'no_of_child', 'no_of_adult'));
    }

    // this function return room according to type
    public function get_room_type_by(Request $request)
    {
        $type_id = $request->input('type_id');

        $arrival_date_time = $this->commonUtil->uf_date($request->arrival_date) . ' ' . $this->commonUtil->uf_time($request->arrival_time);

        $departure_date_time = $this->commonUtil->uf_date($request->departure_date) . ' ' . $this->commonUtil->uf_time($request->departure_time);

        $type = HmsRoomType::find($type_id);
        $existing_rooms = [];

        if (!empty($request->input('room_ids'))) {
            $existing_rooms = $request->input('room_ids');
        }

        $rooms = HmsRoom::non_booking_rooms($type_id, $arrival_date_time, $departure_date_time, $existing_rooms, $this->commonUtil->uf_date($request->arrival_date), $this->commonUtil->uf_date($request->departure_date));

        return view('hms::bookings.room_type_by', compact('rooms', 'type'));
    }

    // this function view after select room during booking with calculation
    public function get_room_detail(Request $request)
    {
        $currentIndex = $request->input('current_index');
        $type = HmsRoomType::find($request->input('type_id'));
        $room = HmsRoom::find($request->input('room_id'));
        $no_of_child = $request->input('no_of_child');
        $no_of_adult = $request->input('no_of_adult');
        $is_edit = true;

        if ($request->input('is_edit')) {
            $is_edit = false;
        }

        $arrival_date = $this->commonUtil->uf_date($request->input('arrival_date'));
        $departure_date = $this->commonUtil->uf_date($request->input('departure_date'));
        // Parse the input dates using Carbon
        $start = Carbon::parse($arrival_date);
        $end = Carbon::parse($departure_date);
        // Calculate the difference in days
        $difference_in_days = $end->diffInDays($start);

        $price = $this->get_price($type->id, $arrival_date, $no_of_adult, $no_of_child);

        if ($difference_in_days <= 0) {
            ++$difference_in_days;
        }

        $total_price = ($difference_in_days * $price);

        $data = [
            'no_of_child' => $no_of_child,
            'no_of_adult' => $no_of_adult,
            'total_price' => $total_price,
            'price' => $price,
        ];

        return view('hms::bookings.room_detail', compact('type', 'room', 'data', 'currentIndex', 'is_edit'));
    }

    // return price according to start day from pricing table
    public function get_price($type_id, $arrival_date, $no_of_adult, $no_of_child)
    {
        // Create a Carbon instance from the date string
        $carbon_date = Carbon::createFromFormat('Y-m-d', $arrival_date);
        // Get the day of the week as a string (e.g., "Sunday")
        $price_day = strtolower($carbon_date->format('l'));

        $price_column = 'price_' . $price_day;

        $pricing = HmsRoomTypePricing::where('adults', $no_of_adult)->where('childrens', $no_of_child)->where('hms_room_type_id', $type_id)->first();

        if ($pricing) {
            $price = $pricing->$price_column;

            if (is_null($price)) {
                return $this->day_wise_or_default_price($type_id, $price_column);
            }
            return $price;
        }

        return $this->day_wise_or_default_price($type_id, $price_column);
    }

    // return price according to day if null return default price
    public function day_wise_or_default_price($type_id, $price_day)
    {
        $pricing = HmsRoomTypePricing::whereNull('adults')->whereNull('childrens')->where('hms_room_type_id', $type_id)->first();

        if (!is_null($pricing->$price_day)) {
            return $pricing->$price_day;
        }
        return $pricing->default_price_per_night;
    }

    // display list of booking in calender view

    public function calendar(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $types = HmsRoomType::where('business_id', $business_id)->pluck('type', 'id')->toArray();

        $rooms = HmsRoom::leftjoin('hms_room_types as type', 'type.id', '=', 'hms_rooms.hms_room_type_id')
            ->where('type.business_id', $business_id)
            ->select('hms_rooms.*', 'type.type', 'type.id as type_id');

        if ($request->type_id) {
            $rooms = $rooms->where('type.id', $request->type_id);
        }

        $rooms = $rooms->get();

        $start_date = now();

        // return $start_date;

        if ($request->day_next) {
            $start_date = now()->startOfWeek()->addDays($request->day_next);
        }

        if ($request->week_next) {
            $start_date = $start_date->addWeeks($request->week_next);
        }

        if ($request->date) {
            $start_date = Carbon::parse($request->date);
        }

        $date_html = '';
        $html = '';
        $class = '';
        $header_date = $start_date->copy();

        for ($i = 0; $i <= 6; $i++) {
            $header_date = $start_date->copy();

            if ($request->day_next) {
                // Clone the $header_date object to avoid modifying it
                $current_date = $header_date->clone();

                // Add $i days to the current date
                $current_date->addDays($i);

                if ($current_date->format('Y-m-d') == now()->format('Y-m-d')) {
                    $class = 'bg-success';
                }
                // Generate the HTML for the table header
                $date_html .= '<th style="width: 100px;" class="text-center ' . $class . '">
                                ' . $current_date->format('d') . ' <br>
                                ' . $current_date->format('l') . '
                                </th>';
            } else {
                if ($header_date->startOfWeek()->addDays($i)->format('Y-m-d') == now()->format('Y-m-d')) {
                    $class = 'bg-success';
                }

                $date_html .= '<th style="width: 100px;" class="text-center ' . $class . '">
                    ' . $header_date->startOfWeek()->addDays($i)->format('d') . ' <br>
                    ' . $header_date->startOfWeek()->addDays($i)->format('l') . '
                    </th>';
            }
            $class = '';
        }

        foreach ($rooms as $room) {
            $html .= '<tr><th class="text-center">' . $room->room_number . ' <br> <small>' . $room->type . '</small/></th>';

            $ref_no = '';

            $size = 100;

            $temp_size = 100;

            for ($j = 0; $j <= 6; $j++) {
                $row_date = $start_date->copy();
                $days = $j;

                if ($request->day_next) {
                    $date = $row_date->addDays($days)->format('Y-m-d');
                } else {
                    $date = $row_date->startOfWeek()->addDays($days)->format('Y-m-d');
                }

                $is_booking = $this->is_booking($date, $room->id);
                if ($is_booking) {
                    if ($ref_no == $is_booking->ref_no) {
                        $size = $size + 100;
                        $html .= '<td></td>';
                    } else {
                        $html .= '<td><div class="hotel-reservation-outer tooltip-demo">
                        <a href="' . action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index']) . '" class="hotel-reservation" data-toggle="tooltip" data-html="true" data-placement="bottom" title="" style=" left: 0%;" data-original-title="' . $is_booking->email . '<br></a>Phone: ' . $is_booking->mobile . '<br/>Adults: ' . $is_booking->adults . ', Children: ' . $is_booking->childrens . '<br/>ID: ' . $is_booking->ref_no . '">
                            <div class="hotel-reservation-inner bg-confirmed" style="width: 100%;"' . $is_booking->ref_no . '><strong>' . $is_booking->name . '</strong></div>
                        </a>
                        </div></td>';
                        $ref_no = $is_booking->ref_no;
                        $size = 100;
                        $temp_size = 100;
                    }
                } else {
                    $html .= '<td class="text-center add_booking">
                        <div class="add_booking_div"><a title="Add Booking" href="' . action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'create']) . '?booking_date=' . $date . '"><i class="fa fa-fw fa-plus"></i></a></div>
                    </td>';
                }
                if ($is_booking) {
                    $ref_no = $is_booking->ref_no;
                } else {
                    $ref_no = '';
                }

                if ($size >= 100) {
                    $html = str_replace('style="width: ' . $temp_size . '%;"' . $ref_no . '', 'style="width: ' . $size . '%;"' . $ref_no . '', $html);
                    $temp_size = $size;
                }
            }
            $html .= '</tr>';
        }
        return view('hms::bookings.calender', compact('types', 'rooms', 'start_date', 'html', 'date_html'));
    }

    public function is_booking($date, $id)
    {
        $bookings = HmsBookingLine::leftjoin('transactions', 'transactions.id', '=', 'hms_booking_lines.transaction_id')
            ->where('hms_booking_lines.hms_room_id', $id)
            ->whereDate('transactions.hms_booking_arrival_date_time', '<=', $date)
            ->whereDate('transactions.hms_booking_departure_date_time', '>=', $date)
            ->where('transactions.status', 'confirmed')
            ->leftJoin('contacts AS c', 'transactions.contact_id', '=', 'c.id')
            ->first();

        return $bookings;
    }

    public function print($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $business = Business::find($business_id);

        $transaction = Transaction::where('transactions.business_id', $business_id)
            ->with(['contact'])
            ->leftJoin('hms_booking_lines as hbl', 'transactions.id', '=', 'hbl.transaction_id')
            ->leftJoin('hms_booking_extras as hbe', 'transactions.id', '=', 'hbe.transaction_id')
            ->leftJoin('hms_coupons as coupons', 'transactions.hms_coupon_id', '=', 'coupons.id')
            ->where('transactions.type', 'hms_booking')
            ->select(
                'transactions.*',
                DB::raw('(SELECT SUM(total_price) FROM hms_booking_lines WHERE transaction_id = transactions.id) as room_price'),
                DB::raw('(SELECT SUM(price) FROM hms_booking_extras WHERE transaction_id = transactions.id) as extra_price'),
                'coupons.coupon_code', DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
            TP.transaction_id=transactions.id) as total_paid')
            )
            ->groupBy('transactions.id')  // Group by transaction ID
            ->findOrFail($id);

        $booking_rooms = HmsBookingLine::where('transaction_id', $id)
            ->leftjoin('hms_rooms as room', 'room.id', '=', 'hms_booking_lines.hms_room_id')
            ->leftjoin('hms_room_types as type', 'type.id', '=', 'hms_booking_lines.hms_room_type_id')
            ->get();

        $extras_id = HmsBookingExtra::where('transaction_id', $id)->pluck('hms_extra_id')->toArray();

        $extras = HmsExtra::where('business_id', $business_id)->get();

        $html = view('hms::bookings.print_pdf')->with(compact('business', 'transaction', 'booking_rooms', 'extras_id', 'extras'))->render();
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => public_path('uploads/temp'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ]);
        $mpdf->useSubstitutions = true;
        $mpdf->SetTitle(__('hms::lang.booking') . ' |' . $transaction->ref_no);
        $mpdf->WriteHTML($html);
        $mpdf->Output('booking.pdf', 'I');
    }

    public function get_check_in_out($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $transaction = HmsTransactionClass::where('transactions.business_id', $business_id)
            ->with(['contact'])
            ->leftJoin('hms_booking_lines as hbl', 'transactions.id', '=', 'hbl.transaction_id')
            ->leftJoin('hms_booking_extras as hbe', 'transactions.id', '=', 'hbe.transaction_id')
            ->leftJoin('hms_coupons as coupons', 'transactions.hms_coupon_id', '=', 'coupons.id')
            ->where('transactions.type', 'hms_booking')
            ->select(
                'transactions.*',
                DB::raw('(SELECT SUM(total_price) FROM hms_booking_lines WHERE transaction_id = transactions.id) as room_price'),
                DB::raw('(SELECT SUM(price) FROM hms_booking_extras WHERE transaction_id = transactions.id) as extra_price'),
                'coupons.coupon_code', DB::raw('COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                            TP.transaction_id=transactions.id), 0) as total_paid')
            )
            ->groupBy('transactions.id')
            ->findOrFail($id);

        $extras_id = HmsBookingExtra::where('transaction_id', $id)->pluck('hms_extra_id')->toArray();

        $booking_rooms = HmsBookingLine::where('transaction_id', $id)
            ->leftjoin('hms_rooms as room', 'room.id', '=', 'hms_booking_lines.hms_room_id')
            ->leftjoin('hms_room_types as type', 'type.id', '=', 'hms_booking_lines.hms_room_type_id')
            ->get();

        $extras = HmsExtra::where('business_id', $business_id)->get();
        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        return view('hms::bookings.check_in_out', compact('extras', 'transaction', 'extras_id', 'booking_rooms', 'payment_types'));
    }

    public function get_extend_stay($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.extend_stay')) {
            abort(403, 'Unauthorized action.');
        }

        $transaction = Transaction::where('transactions.business_id', $business_id)
            ->where('type', 'hms_booking')
            ->findOrFail($id);

        $booking_room = HmsBookingLine::where('transaction_id', $id)
            ->leftJoin('hms_rooms as room', 'room.id', '=', 'hms_booking_lines.hms_room_id')
            ->leftJoin('hms_room_types as type', 'type.id', '=', 'hms_booking_lines.hms_room_type_id')
            ->select('hms_booking_lines.*', 'room.room_number', 'type.type as type_name')
            ->first();

        $room_rate = $booking_room ? $booking_room->price : 0;

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('hms::bookings.extend_stay', compact('transaction', 'booking_room', 'room_rate', 'payment_line', 'payment_types', 'accounts'));
    }

    public function post_extend_stay(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('hms.extend_stay')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'hms_booking')
                ->findOrFail($id);

            $new_departure_raw = $request->input('new_departure_datetime');
            $new_departure = $this->commonUtil->uf_date($new_departure_raw) . ' ' . Carbon::parse($transaction->hms_booking_departure_date_time)->format('H:i:s');

            $old_departure = $transaction->hms_booking_departure_date_time;

            if (Carbon::parse($new_departure)->lte(Carbon::parse($old_departure))) {
                return back()->with('status', ['success' => 0, 'msg' => __('hms::lang.new_departure_must_be_after_current')])->withInput();
            }

            $extra_nights = max(1, Carbon::parse($new_departure)->startOfDay()->diffInDays(Carbon::parse($old_departure)->startOfDay()));

            $booking_line = HmsBookingLine::where('transaction_id', $id)->first();
            $room_rate = $booking_line ? $booking_line->price : 0;

            $additional_charges = $extra_nights * $room_rate;

            // Update booking line total_price
            if ($booking_line) {
                $booking_line->total_price = $booking_line->total_price + ($extra_nights * $room_rate);
                $booking_line->save();
            }

            // Update transaction
            $transaction->hms_booking_departure_date_time = $new_departure;
            $transaction->final_total = $transaction->final_total + $additional_charges;
            $transaction->total_before_tax = $transaction->total_before_tax + $additional_charges;
            $transaction->save();

            HmsStayExtension::create([
                'transaction_id'        => $transaction->id,
                'old_departure_datetime' => $old_departure,
                'new_departure_datetime' => $new_departure,
                'extra_nights'          => $extra_nights,
                'additional_charges'    => $additional_charges,
                'extended_by'           => auth()->id(),
                'note'                  => $request->input('note'),
            ]);

            // Process payment if provided (add new payment without touching existing ones)
            $payment_input = $request->input('payment', []);
            foreach ($payment_input as $payment) {
                $payment_amount = $this->commonUtil->num_uf($payment['amount'] ?? 0);
                if ($payment_amount <= 0) {
                    continue;
                }

                $ref_count = $this->transactionUtil->setAndGetReferenceCount('sell_payment', $business_id);
                $payment_ref_no = $this->transactionUtil->generateReferenceNumber('sell_payment', $ref_count, $business_id);

                $payment_data = [
                    'transaction_id'          => $transaction->id,
                    'amount'                  => $payment_amount,
                    'method'                  => $payment['method'] ?? 'cash',
                    'business_id'             => $transaction->business_id,
                    'is_return'               => 0,
                    'card_transaction_number' => $payment['card_transaction_number'] ?? null,
                    'card_number'             => $payment['card_number'] ?? null,
                    'card_type'               => $payment['card_type'] ?? null,
                    'card_holder_name'        => $payment['card_holder_name'] ?? null,
                    'card_month'              => $payment['card_month'] ?? null,
                    'card_security'           => $payment['card_security'] ?? null,
                    'cheque_number'           => $payment['cheque_number'] ?? null,
                    'bank_account_number'     => $payment['bank_account_number'] ?? null,
                    'note'                    => $payment['note'] ?? null,
                    'paid_on'                 => Carbon::now()->toDateTimeString(),
                    'created_by'              => auth()->id(),
                    'payment_for'             => $transaction->contact_id,
                    'payment_ref_no'          => $payment_ref_no,
                    'account_id'              => !empty($payment['account_id']) && ($payment['method'] ?? '') != 'advance' ? $payment['account_id'] : null,
                ];

                for ($i = 1; $i < 8; $i++) {
                    if (($payment['method'] ?? '') === 'custom_pay_' . $i) {
                        $payment_data['transaction_no'] = $payment["transaction_no_{$i}"] ?? null;
                    }
                }

                $created_payment = TransactionPayment::create($payment_data);

                $event_data = $payment_data;
                $event_data['transaction_type'] = $transaction->type;
                event(new \App\Events\TransactionPaymentAdded($created_payment, $event_data));
            }

            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            DB::commit();

            $output = ['success' => 1, 'msg' => __('hms::lang.stay_extended_successfully')];
            return redirect()
                ->action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index'])
                ->with('status', $output);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            return back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function get_change_room($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $transaction = Transaction::where('transactions.business_id', $business_id)
            ->where('type', 'hms_booking')
            ->findOrFail($id);

        $booking_rooms = HmsBookingLine::where('transaction_id', $id)
            ->leftJoin('hms_rooms as room', 'room.id', '=', 'hms_booking_lines.hms_room_id')
            ->leftJoin('hms_room_types as type', 'type.id', '=', 'hms_booking_lines.hms_room_type_id')
            ->select('hms_booking_lines.*', 'room.room_number', 'type.type as type_name')
            ->get();

        $room_types = HmsRoomType::where('business_id', $business_id)
            ->whereRaw('EXISTS (SELECT 1 FROM hms_room_type_pricings WHERE hms_room_type_id = hms_room_types.id)')
            ->pluck('type', 'id')
            ->toArray();

        $today = Carbon::today();
        $departure = Carbon::parse($transaction->hms_booking_departure_date_time)->startOfDay();
        $arrival = Carbon::parse($transaction->hms_booking_arrival_date_time)->startOfDay();
        $nights_stayed = max(0, (int) $today->diffInDays($arrival, false) * -1);
        $nights_remaining = max(1, (int) $today->diffInDays($departure));

        return view('hms::bookings.change_room', compact(
            'transaction', 'booking_rooms', 'room_types', 'nights_stayed', 'nights_remaining'
        ));
    }

    public function get_change_room_type_by(Request $request)
    {
        $type_id = $request->input('type_id');
        $current_room_id = $request->input('current_room_id');
        $arrival_date_time = $request->input('arrival_date_time');
        $departure_date_time = $request->input('departure_date_time');
        $adate = Carbon::parse($arrival_date_time)->format('Y-m-d');
        $ddate = Carbon::parse($departure_date_time)->format('Y-m-d');

        $existing_rooms = $current_room_id ? [$current_room_id] : [];

        $rooms = HmsRoom::non_booking_rooms($type_id, $arrival_date_time, $departure_date_time, $existing_rooms, $adate, $ddate);

        return view('hms::bookings.change_room_rooms', compact('rooms'));
    }

    public function get_change_room_price(Request $request)
    {
        $type_id = $request->input('type_id');
        $arrival_date = $request->input('arrival_date');
        $no_of_adult = $request->input('no_of_adult');
        $no_of_child = $request->input('no_of_child');

        $price = $this->get_price($type_id, $arrival_date, $no_of_adult, $no_of_child);

        return response()->json(['price' => $price]);
    }

    public function post_change_room(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'hms_booking')
                ->findOrFail($id);

            $booking_line_id  = $request->input('line_id');
            $new_room_id      = $request->input('new_room_id');
            $new_type_id      = $request->input('new_room_type_id');
            $note             = $request->input('notes');

            $booking_line = HmsBookingLine::findOrFail($booking_line_id);
            $from_room_id = $booking_line->hms_room_id;
            $from_type_id = $booking_line->hms_room_type_id;
            $old_price    = (float) $booking_line->price;

            // Compute nights server-side
            $today            = Carbon::today();
            $arrival          = Carbon::parse($transaction->hms_booking_arrival_date_time)->startOfDay();
            $departure        = Carbon::parse($transaction->hms_booking_departure_date_time)->startOfDay();
            $nights_stayed    = max(0, (int) $today->diffInDays($arrival, false) * -1);
            $nights_remaining = max(1, (int) $today->diffInDays($departure));

            // Validate new room is not already booked for this period (excluding current transaction)
            $conflict = HmsBookingLine::where('hms_room_id', $new_room_id)
                ->whereHas('transaction', function ($q) use ($transaction) {
                    $q->where('id', '!=', $transaction->id)
                      ->where('type', 'hms_booking')
                      ->where('status', '!=', 'cancelled')
                      ->where('hms_booking_arrival_date_time', '<', $transaction->hms_booking_departure_date_time)
                      ->where('hms_booking_departure_date_time', '>', $transaction->hms_booking_arrival_date_time);
                })->exists();

            if ($conflict) {
                return response()->json(['success' => 0, 'msg' => __('hms::lang.room_not_available')]);
            }

            $arrival_ymd = $arrival->format('Y-m-d');
            $new_price = $this->get_price($new_type_id, $arrival_ymd, $booking_line->adults, $booking_line->childrens);

            $price_difference = ($new_price - $old_price) * $nights_remaining;
            $new_total_price  = ($old_price * $nights_stayed) + ($new_price * $nights_remaining);

            $booking_line->hms_room_id      = $new_room_id;
            $booking_line->hms_room_type_id = $new_type_id;
            $booking_line->price            = $new_price;
            $booking_line->total_price      = $new_total_price;
            $booking_line->save();

            if ($price_difference > 0) {
                $transaction->final_total      = $transaction->final_total + $price_difference;
                $transaction->total_before_tax = $transaction->total_before_tax + $price_difference;
                $transaction->save();
            }

            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            HmsRoomChangeLog::create([
                'transaction_id'      => $transaction->id,
                'booking_line_id'     => $booking_line_id,
                'from_room_id'        => $from_room_id,
                'from_room_type_id'   => $from_type_id,
                'to_room_id'          => $new_room_id,
                'to_room_type_id'     => $new_type_id,
                'nights_stayed'       => $nights_stayed,
                'nights_remaining'    => $nights_remaining,
                'old_price_per_night' => $old_price,
                'new_price_per_night' => $new_price,
                'price_difference'    => $price_difference,
                'changed_by'          => auth()->id(),
                'note'                => $note,
            ]);

            DB::commit();

            return response()->json(['success' => 1, 'msg' => __('hms::lang.room_changed_successfully')]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            return response()->json(['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function post_check_in_out(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $transaction = Transaction::where('business_id', $business_id)
            ->findOrFail($id);

        try {
            if (!empty($request->in_out_date_time)) {
                $in_out_date_time = $this->commonUtil->uf_date($request->in_out_date_time, true);
            }

            $check_in = $transaction->check_in;

            if (empty($check_in)) {
                $transaction->check_in = $in_out_date_time;
            }

            if (!empty($check_in)) {
                // Process inline payment if provided during checkout
                $payment_amount = (float) $request->input('payment_amount', 0);
                if ($payment_amount > 0) {
                    DB::table('transaction_payments')->insert([
                        'transaction_id' => $transaction->id,
                        'business_id'    => $business_id,
                        'amount'         => $payment_amount,
                        'method'         => $request->input('payment_method', 'cash'),
                        'paid_on'        => now(),
                        'created_by'     => auth()->id(),
                        'payment_for'    => $transaction->contact_id,
                    ]);
                    $totalPaid = DB::table('transaction_payments')
                        ->where('transaction_id', $transaction->id)
                        ->where('is_return', 0)
                        ->sum('amount');
                    $transaction->payment_status = $totalPaid >= $transaction->final_total ? 'paid' : 'partial';
                }

                $transaction->check_out = $in_out_date_time;
            }

            $transaction->update();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            // If this action is a Check-In, return to index and print via AJAX
            if (empty($check_in)) {
                return redirect()
                    ->action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index'])
                    ->with('status', $output)
                    ->with('print_receipt_booking_id', $transaction->id);
            }

            return redirect()
                ->action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index'])
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

    public function get_available_rooms_for_change(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'hms_booking')
            ->findOrFail($request->transaction_id);

        $line = HmsBookingLine::where('transaction_id', $transaction->id)
            ->findOrFail($request->line_id);

        $arrival_date_time  = $transaction->hms_booking_arrival_date_time;
        $departure_date_time = $transaction->hms_booking_departure_date_time;
        $adate = \Carbon\Carbon::parse($arrival_date_time)->format('Y-m-d');
        $ddate = \Carbon\Carbon::parse($departure_date_time)->format('Y-m-d');

        // All other room IDs in this booking (exclude the current line's room)
        $existing_rooms = HmsBookingLine::where('transaction_id', $transaction->id)
            ->where('id', '!=', $line->id)
            ->pluck('hms_room_id')
            ->toArray();

        $rooms = HmsRoom::non_booking_rooms(
            $request->type_id,
            $arrival_date_time,
            $departure_date_time,
            $existing_rooms,
            $adate,
            $ddate
        );

        $days = max(1, \Carbon\Carbon::parse($adate)->diffInDays(\Carbon\Carbon::parse($ddate)));
        $price_per_night = $this->get_price($request->type_id, $adate, $line->adults, $line->childrens);
        $new_total = $price_per_night * $days;
        $price_diff = $new_total - $line->total_price;

        return response()->json([
            'rooms'           => $rooms,
            'price_per_night' => $price_per_night,
            'new_total'       => $new_total,
            'price_diff'      => $price_diff,
            'old_total'       => $line->total_price,
        ]);
    }

}
