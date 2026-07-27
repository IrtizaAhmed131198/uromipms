<?php
namespace Modules\Hms\Exports;

use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GeneralGuestReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private array $filters,
        private int $business_id
    ) {}

    public function title(): string
    {
        return 'General Guest Report';
    }

    public function headings(): array
    {
        return [
            'Booking #', 'Guest Name', 'Phone', 'ID Number', 'Address',
            'Room No', 'Room Type', 'Rate/Night', 'Guests',
            'Arrival Date', 'Departure Date', 'Check-In', 'Check-Out', 'Staff',
            'Ext. Nights', 'Ext. Charge', 'Old Room', 'New Room', 'Room Changed On',
            'Room Charge', 'Total Bill', 'Paid', 'Balance', 'Payment Method', 'Status',
        ];
    }

    public function array(): array
    {
        $rows = $this->buildQuery()->get();
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                $row->ref_no,
                $row->guest_name,
                $row->mobile,
                $row->id_number,
                $row->address,
                $row->room_number,
                $row->room_type,
                $row->room_rate,
                $row->total_guests,
                $row->hms_booking_arrival_date_time,
                $row->hms_booking_departure_date_time,
                $row->check_in,
                $row->check_out,
                $row->staff_name,
                $row->ext_nights ?? 0,
                $row->ext_charges ?? 0,
                $row->from_room_number ?? '',
                $row->to_room_number ?? '',
                $row->changed_at ?? '',
                $row->hbl_total_price ?? 0,
                $row->final_total,
                $row->total_paid,
                $row->balance,
                $row->pay_methods ?? '',
                $row->status,
            ];
        }

        // Grand total row
        $data[] = [
            'GRAND TOTAL', '', '', '', '', '', '', '', '',
            '', '', '', '', '',
            array_sum(array_column($data, 14)),
            array_sum(array_column($data, 15)),
            '', '', '',
            array_sum(array_column($data, 19)),
            array_sum(array_column($data, 20)),
            array_sum(array_column($data, 21)),
            array_sum(array_column($data, 22)),
            '', '',
        ];

        return $data;
    }

    private function buildQuery()
    {
        $query = Transaction::where('transactions.business_id', $this->business_id)
            ->where('transactions.type', 'hms_booking')
            ->leftJoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->leftJoin('hms_booking_lines as hbl', 'hbl.transaction_id', '=', 'transactions.id')
            ->leftJoin('hms_rooms as room', 'room.id', '=', 'hbl.hms_room_id')
            ->leftJoin('hms_room_types as rtype', 'rtype.id', '=', 'hbl.hms_room_type_id')
            ->leftJoin(DB::raw('(SELECT transaction_id,
                SUM(IF(is_return=0,amount,-amount)) as total_paid,
                GROUP_CONCAT(DISTINCT method SEPARATOR ", ") as pay_methods
                FROM transaction_payments GROUP BY transaction_id) as tp'),
                'tp.transaction_id', '=', 'transactions.id')
            ->leftJoin(DB::raw('(SELECT transaction_id,
                SUM(additional_charges) as ext_charges,
                SUM(extra_nights) as ext_nights
                FROM hms_stay_extensions GROUP BY transaction_id) as se'),
                'se.transaction_id', '=', 'transactions.id')
            ->leftJoin(DB::raw('(SELECT transaction_id,
                from_room_id, to_room_id, created_at as changed_at
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
                DB::raw('COALESCE(NULLIF(c.name,""), c.supplier_business_name) as guest_name'),
                'c.mobile', 'c.contact_id as id_number',
                DB::raw('CONCAT_WS(", ", NULLIF(c.city,""), NULLIF(c.state,""), NULLIF(c.country,"")) as address'),
                'room.room_number', 'rtype.type as room_type', 'hbl.price as room_rate',
                'hbl.total_price as hbl_total_price',
                DB::raw('(hbl.adults + hbl.childrens) as total_guests'),
                DB::raw('CONCAT(u.first_name," ",IFNULL(u.last_name,"")) as staff_name'),
                DB::raw('COALESCE(tp.total_paid, 0) as total_paid'), 'tp.pay_methods',
                DB::raw('(transactions.final_total - COALESCE(tp.total_paid,0)) as balance'),
                'se.ext_charges', 'se.ext_nights',
                'from_room.room_number as from_room_number', 'to_room.room_number as to_room_number',
                'hrcl.changed_at',
            ])
            ->groupBy('transactions.id');

        $filters = $this->filters;

        if (!empty($filters['date_from'])) {
            $query->where('transactions.hms_booking_arrival_date_time', '>=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $query->where('transactions.hms_booking_arrival_date_time', '<=', $filters['date_to'] . ' 23:59:59');
        }
        if (!empty($filters['room_id'])) {
            $query->where('room.id', $filters['room_id']);
        }
        if (!empty($filters['guest_name'])) {
            $query->where(DB::raw('COALESCE(NULLIF(c.name,""), c.supplier_business_name)'), 'like', '%' . $filters['guest_name'] . '%');
        }
        if (!empty($filters['staff_id'])) {
            $query->where('transactions.created_by', $filters['staff_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('transactions.status', $filters['status']);
        }

        // Tab filters
        $tab = $filters['tab_type'] ?? 'general';
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
