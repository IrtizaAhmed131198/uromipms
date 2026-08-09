<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\InventoryRequest;
use App\InventoryRequestLine;
use App\Product;
use App\Variation;
use App\Category;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Transaction;

class InventoryRequestController extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        if (!auth()->user()->can('inventory_request.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $inventory_requests = InventoryRequest::where('inventory_requests.business_id', $business_id)
                ->with(['lines.product', 'lines.variation'])
                ->join('business_locations as sl', 'inventory_requests.source_location_id', '=', 'sl.id')
                ->join('business_locations as dl', 'inventory_requests.destination_location_id', '=', 'dl.id')
                ->join('users as u', 'inventory_requests.requested_by', '=', 'u.id')
                ->select([
                    'inventory_requests.id',
                    'inventory_requests.request_number',
                    'sl.name as source_location',
                    'dl.name as destination_location',
                    'inventory_requests.status',
                    'inventory_requests.created_at',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as requested_by")
                ]);

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $inventory_requests->whereDate('inventory_requests.created_at', '>=', $start)
                                   ->whereDate('inventory_requests.created_at', '<=', $end);
            }

            if (!empty(request()->source_location_id)) {
                $inventory_requests->where('inventory_requests.source_location_id', request()->source_location_id);
            }

            if (!empty(request()->destination_location_id)) {
                $inventory_requests->where('inventory_requests.destination_location_id', request()->destination_location_id);
            }

            if (!empty(request()->status)) {
                $inventory_requests->where('inventory_requests.status', request()->status);
            }

            if (!empty(request()->requested_by)) {
                $inventory_requests->where('inventory_requests.requested_by', request()->requested_by);
            }

            if (!empty(request()->approved_by)) {
                $inventory_requests->where('inventory_requests.approved_by', request()->approved_by);
            }

            if (!empty(request()->product_id)) {
                $inventory_requests->whereHas('lines', function ($q) {
                    $q->where('product_id', request()->product_id);
                });
            }

            if (!empty(request()->category_id)) {
                $inventory_requests->whereHas('lines.product', function ($q) {
                    $q->where('category_id', request()->category_id);
                });
            }

            return Datatables::of($inventory_requests)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                                __("messages.actions") .
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li><a href="' . route('inventory-requests.show', [$row->id]) . '"><i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
                    
                    if ($row->status == 'Pending Approval' && auth()->user()->can('inventory_request.approve')) {
                        $html .= '<li><a href="' . route('inventory-requests.edit', [$row->id]) . '"><i class="fas fa-check-circle"></i> Approve / Reject</a></li>';
                    }

                    if (in_array($row->status, ['Approved', 'Partially Approved']) && auth()->user()->can('inventory_request.accept')) {
                        $html .= '<li><a href="#" class="accept-request" data-href="' . route('inventory-requests.accept', [$row->id]) . '"><i class="fas fa-check"></i> Accept Stock</a></li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->editColumn('status', function ($row) {
                    $badges = [
                        'Pending Approval' => 'bg-yellow',
                        'Approved' => 'bg-green',
                        'Partially Approved' => 'bg-light-blue',
                        'Rejected' => 'bg-red',
                        'Accepted' => 'bg-green',
                        'Completed' => 'bg-green'
                    ];
                    $bg = $badges[$row->status] ?? 'bg-gray';
                    return '<span class="label ' . $bg . '">' . $row->status . '</span>';
                })
                ->addColumn('products', function ($row) {
                    $html = [];
                    foreach ($row->lines as $line) {
                        if ($line->product) {
                            $name = $line->product->name;
                            if ($line->product->type == 'variable' && $line->variation) {
                                $name .= ' - ' . $line->variation->name;
                            }
                            $html[] = $name;
                        }
                    }
                    return implode('<br>', $html);
                })
                ->addColumn('qty_requested', function ($row) {
                    $html = [];
                    foreach ($row->lines as $line) {
                        $html[] = number_format($line->quantity_requested, 1, '.', '');
                    }
                    return implode('<br>', $html);
                })
                ->addColumn('qty_approved', function ($row) {
                    $html = [];
                    foreach ($row->lines as $line) {
                        $html[] = number_format((float)$line->quantity_approved, 1, '.', '');
                    }
                    return implode('<br>', $html);
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->rawColumns(['action', 'status', 'products', 'qty_requested', 'qty_approved'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $categories = Category::forDropdown($business_id, 'product');
        $users = User::forDropdown($business_id, false);
        $products = Product::where('business_id', $business_id)->pluck('name', 'id');
        $statuses = [
            'Pending Approval' => __('Pending Approval'),
            'Approved' => __('Approved'),
            'Partially Approved' => __('Partially Approved'),
            'Rejected' => __('Rejected'),
            'Completed' => __('Completed'),
        ];

        return view('inventory_requests.index', compact('business_locations', 'categories', 'users', 'products', 'statuses'));
    }

    public function pendingAcceptance(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $location_id = $request->input('location_id');

        if (request()->ajax()) {
            $inventory_requests = InventoryRequest::where('inventory_requests.business_id', $business_id)
                ->where('inventory_requests.destination_location_id', $location_id)
                ->whereIn('inventory_requests.status', ['Approved', 'Completed'])
                ->with(['lines.product', 'lines.variation'])
                ->join('business_locations as sl', 'inventory_requests.source_location_id', '=', 'sl.id')
                ->join('users as u', 'inventory_requests.requested_by', '=', 'u.id')
                ->select([
                    'inventory_requests.id',
                    'inventory_requests.request_number',
                    'sl.name as source_location',
                    'inventory_requests.status',
                    'inventory_requests.created_at',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as requested_by")
                ]);

            return Datatables::of($inventory_requests)
                ->editColumn('status', function ($row) {
                    $badges = [
                        'Pending Approval' => 'bg-yellow',
                        'Approved' => 'bg-green',
                        'Partially Approved' => 'bg-light-blue',
                        'Rejected' => 'bg-red',
                        'Accepted' => 'bg-green',
                        'Completed' => 'bg-green'
                    ];
                    $bg = $badges[$row->status] ?? 'bg-gray';
                    return '<span class="label ' . $bg . '">' . $row->status . '</span>';
                })
                ->addColumn('action', function ($row) {
                    if (in_array($row->status, ['Approved', 'Partially Approved'])) {
                        return '<button type="button" class="btn btn-primary btn-sm accept-pending-request" data-href="' . route('inventory-requests.accept', [$row->id]) . '"><i class="fas fa-check"></i> Accept</button>';
                    }
                    return '';
                })
                ->addColumn('products', function ($row) {
                    $html = [];
                    foreach ($row->lines as $line) {
                        if ($line->product) {
                            $name = $line->product->name;
                            if ($line->product->type == 'variable' && $line->variation) {
                                $name .= ' - ' . $line->variation->name;
                            }
                            $html[] = $name . ' (Qty: ' . number_format((float)$line->quantity_approved, 1, '.', '') . ')';
                        }
                    }
                    return implode('<br>', $html);
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->rawColumns(['action', 'products', 'status'])
                ->make(true);
        }
    }

    public function create()
    {
        if (!auth()->user()->can('inventory_request.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('inventory_requests.create', compact('business_locations'));
    }

    public function store(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();

            $inventoryRequest = InventoryRequest::create([
                'business_id' => $business_id,
                'request_number' => 'REQ-' . time(),
                'source_location_id' => $request->input('source_location_id'),
                'destination_location_id' => $request->input('destination_location_id'),
                'requested_by' => auth()->user()->id,
                'status' => 'Pending Approval',
                'notes' => $request->input('notes'),
            ]);

            $products = $request->input('products');
            foreach ($products as $product) {
                if (!empty($product['quantity'])) {
                    InventoryRequestLine::create([
                        'inventory_request_id' => $inventoryRequest->id,
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity_requested' => $product['quantity'],
                    ]);
                }
            }

            DB::commit();

            // Send notification to involved users
            $this->notifyInventoryRequestUsers($inventoryRequest, 'created');

            $output = ['success' => 1, 'msg' => __('Inventory request created successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect('inventory-requests')->with('status', $output);
    }

    public function storePosRequest(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();

            $inventoryRequest = InventoryRequest::create([
                'business_id' => $business_id,
                'request_number' => 'REQ-' . time(),
                'source_location_id' => $request->input('source_location_id'),
                'destination_location_id' => $request->input('destination_location_id'),
                'requested_by' => auth()->user()->id,
                'status' => 'Pending Approval',
                'notes' => $request->input('notes'),
            ]);

            $products = $request->input('products');
            if (!empty($products)) {
                foreach ($products as $product) {
                    if (!empty($product['quantity'])) {
                        InventoryRequestLine::create([
                            'inventory_request_id' => $inventoryRequest->id,
                            'product_id' => $product['product_id'],
                            'variation_id' => $product['variation_id'],
                            'quantity_requested' => $product['quantity'],
                        ]);
                    }
                }
            }

            DB::commit();

            // Send notification to involved users
            $this->notifyInventoryRequestUsers($inventoryRequest, 'created');

            return response()->json(['success' => true, 'msg' => __('Inventory request created successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            return response()->json(['success' => false, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function show($id)
    {
        $inventoryRequest = InventoryRequest::with(['lines.product', 'lines.variation', 'sourceLocation', 'destinationLocation', 'requestedBy'])->findOrFail($id);
        return view('inventory_requests.show', compact('inventoryRequest'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('inventory_request.approve')) {
            abort(403, 'Unauthorized action.');
        }
        $inventoryRequest = InventoryRequest::with(['lines.product', 'lines.variation'])->findOrFail($id);
        return view('inventory_requests.edit', compact('inventoryRequest'));
    }

    public function approve(Request $request, $id)
    {
        if (!auth()->user()->can('inventory_request.approve')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            DB::beginTransaction();
            $inventoryRequest = InventoryRequest::findOrFail($id);
            
            $approved_lines = $request->input('approved_lines');
            $status = $request->input('status'); // 'Approved', 'Partially Approved', 'Rejected'
            
            if ($status != 'Rejected') {
                foreach ($approved_lines as $line_id => $qty) {
                    InventoryRequestLine::where('id', $line_id)
                        ->update(['quantity_approved' => $qty]);
                }
            }

            $inventoryRequest->status = $status;
            $inventoryRequest->approved_by = auth()->user()->id;
            $inventoryRequest->save();

            DB::commit();

            // Send notification to requester & destination staff
            $action = strtolower(str_replace(' ', '_', $status));
            $this->notifyInventoryRequestUsers($inventoryRequest, $action);

            $output = ['success' => 1, 'msg' => __('Inventory request processed successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect('inventory-requests')->with('status', $output);
    }

    public function accept(Request $request, $id)
    {
        if (!auth()->user()->can('inventory_request.accept')) {
            return ['success' => 0, 'msg' => 'Unauthorized action.'];
        }
        try {
            DB::beginTransaction();
            $inventoryRequest = InventoryRequest::with(['lines'])->findOrFail($id);
            $business_id = $inventoryRequest->business_id;

            // Mark as accepted
            $inventoryRequest->status = 'Completed';
            $inventoryRequest->accepted_by = auth()->user()->id;
            $inventoryRequest->save();

            // Create transactions to deduct stock from source and add to destination
            $transaction_data = [
                'business_id' => $business_id,
                'location_id' => $inventoryRequest->source_location_id,
                'type' => 'sell_transfer',
                'status' => 'final',
                'payment_status' => 'paid',
                'created_by' => auth()->user()->id,
                'transaction_date' => \Carbon::now(),
                'ref_no' => 'REQ-TR-' . $inventoryRequest->id,
                'total_before_tax' => 0,
                'final_total' => 0,
            ];
            
            $sell_transfer = Transaction::create($transaction_data);
            
            $purchase_transfer_data = $transaction_data;
            $purchase_transfer_data['location_id'] = $inventoryRequest->destination_location_id;
            $purchase_transfer_data['type'] = 'purchase_transfer';
            $purchase_transfer_data['transfer_parent_id'] = $sell_transfer->id;
            
            $purchase_transfer = Transaction::create($purchase_transfer_data);

            $sell_lines = [];
            $purchase_lines = [];
            foreach ($inventoryRequest->lines as $line) {
                $line->quantity_received = $line->quantity_approved;
                $line->save();

                if ($line->quantity_approved > 0) {
                    $dest_product_id = $line->product_id;
                    $dest_variation_id = $line->variation_id;

                    $source_product = \App\Product::find($line->product_id);
                    $source_variation = \App\Variation::find($line->variation_id);

                    if ($source_product && $source_variation) {
                        $matching_product = \App\Product::where('business_id', $business_id)
                            ->where('name', $source_product->name)
                            ->where('category_id', $source_product->category_id)
                            ->whereHas('variations', function($q) use ($source_variation) {
                                $q->where('name', $source_variation->name);
                            })
                            ->first();

                        if ($matching_product) {
                            $matching_variation = \App\Variation::where('product_id', $matching_product->id)
                                ->where('name', $source_variation->name)
                                ->first();
                            
                            if ($matching_variation) {
                                $dest_product_id = $matching_product->id;
                                $dest_variation_id = $matching_variation->id;
                            }
                        }
                    }

                    $sell_lines[] = [
                        'product_id' => $line->product_id,
                        'variation_id' => $line->variation_id,
                        'quantity' => $line->quantity_approved,
                        'unit_price' => 0,
                        'unit_price_inc_tax' => 0,
                        'item_tax' => 0,
                    ];
                    $purchase_lines[] = [
                        'product_id' => $dest_product_id,
                        'variation_id' => $dest_variation_id,
                        'quantity' => $line->quantity_approved,
                        'purchase_price' => 0,
                        'purchase_price_inc_tax' => 0,
                        'item_tax' => 0,
                    ];
                    
                    // Decrease stock from source
                    $this->productUtil->decreaseProductQuantity(
                        $line->product_id,
                        $line->variation_id,
                        $inventoryRequest->source_location_id,
                        $line->quantity_approved
                    );
                    
                    // Increase stock to destination
                    $this->productUtil->updateProductQuantity(
                        $inventoryRequest->destination_location_id,
                        $dest_product_id,
                        $dest_variation_id,
                        $line->quantity_approved
                    );
                }
            }

            if (count($sell_lines) > 0) {
                $sell_transfer->sell_lines()->createMany($sell_lines);
                $purchase_transfer->purchase_lines()->createMany($purchase_lines);
            }

            DB::commit();

            // Send notification to both requester and approver
            $this->notifyInventoryRequestUsers($inventoryRequest, 'completed');

            return ['success' => 1, 'msg' => __('Stock accepted and transferred successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            return ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }
    }

    /**
     * Helper to notify involved users on Inventory Request actions
     *
     * @param \App\InventoryRequest $inventoryRequest
     * @param string $action ('created', 'approved', 'partially_approved', 'rejected', 'completed')
     */
    private function notifyInventoryRequestUsers($inventoryRequest, $action)
    {
        try {
            $currentUser = auth()->user();
            $actorName = $currentUser ? $currentUser->user_full_name : 'System';
            $business_id = $inventoryRequest->business_id;

            $recipientIds = collect();

            if ($action == 'created') {
                // Notify admins/approvers at the source location
                $allUsers = User::where('business_id', $business_id)->get();
                foreach ($allUsers as $user) {
                    if ($user->can('inventory_request.approve') || $user->can('admin') || $user->user_type == 'user') {
                        $permitted = $user->permitted_locations($business_id);
                        if ($permitted == 'all' || (is_array($permitted) && in_array($inventoryRequest->source_location_id, $permitted))) {
                            $recipientIds->push($user->id);
                        }
                    }
                }
            } elseif (in_array($action, ['approved', 'partially_approved', 'rejected'])) {
                // Notify requester
                if (!empty($inventoryRequest->requested_by)) {
                    $recipientIds->push($inventoryRequest->requested_by);
                }
                // Notify destination location staff who accept stock
                $allUsers = User::where('business_id', $business_id)->get();
                foreach ($allUsers as $user) {
                    if ($user->can('inventory_request.accept') || $user->can('admin')) {
                        $permitted = $user->permitted_locations($business_id);
                        if ($permitted == 'all' || (is_array($permitted) && in_array($inventoryRequest->destination_location_id, $permitted))) {
                            $recipientIds->push($user->id);
                        }
                    }
                }
            } elseif (in_array($action, ['completed', 'accepted'])) {
                // Notify both requester and approver
                if (!empty($inventoryRequest->requested_by)) {
                    $recipientIds->push($inventoryRequest->requested_by);
                }
                if (!empty($inventoryRequest->approved_by)) {
                    $recipientIds->push($inventoryRequest->approved_by);
                }
            }

            $recipientIds = $recipientIds->unique();

            foreach ($recipientIds as $userId) {
                $recipient = User::find($userId);
                if ($recipient) {
                    $recipient->notify(new \App\Notifications\InventoryRequestNotification($inventoryRequest, $action, $actorName));
                }
            }
        } catch (\Exception $e) {
            \Log::emergency("InventoryRequest Notification Error File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage());
        }
    }
}
