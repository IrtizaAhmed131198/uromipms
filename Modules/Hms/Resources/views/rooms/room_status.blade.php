@extends('layouts.app')
@section('title', 'Rooms Dashboard')

@section('content')
@include('hms::layouts.nav')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('hms::lang.room_status')
    </h1>
    <p><i class="fa fa-info-circle"></i> @lang('hms::lang.rooms_help_text') </p>
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'All Rooms'])

    <div class="room-grid">
        @forelse($rooms as $room)
        <div class="room-col">

            {{--
                CASE 1: Occupied  (checked_in=1, check_out=null) → RED
                CASE 2: Booked    (is_booked=1, not checked in)  → YELLOW
                CASE 3: Available (is_booked=0)                  → GREEN
            --}}

            @php
                $is_occupied    = !empty($room->is_booked) && !empty($room->is_checked_in);
                $is_booked_only = !empty($room->is_booked) && empty($room->is_checked_in);
                $is_available   = empty($room->is_booked);

                if ($is_occupied) {
                    $status_class = 'is-occupied';
                    $status_label = 'Occupied';
                } elseif ($is_booked_only) {
                    $status_class = 'is-booked';
                    $status_label = 'Booked';
                } else {
                    $status_class = 'is-available';
                    $status_label = 'Available';
                }
            @endphp

            <div class="room-card {{ $status_class }}">
                {{-- BG Image --}}
                <img src="{{ $room->image_url }}" class="room-card-img" alt="Room image">

                {{-- Colored overlay --}}
                <div class="room-card-overlay"></div>

                <div class="room-card-body">
                    <p class="room-floor-label">Floor Name</p>
                    <h4 class="room-floor-name">
                        {{ strtoupper($room->floor_name === null ? 'GROUND' : $room->floor_name) }}
                    </h4>
                    <h3 class="room-number">
                        Room No. {{ $room->room_number ?? $room->id }}
                    </h3>
                    <p class="room-info-item">
                        Room Type : <strong>{{ $room->room_type ?? 'Standard' }}</strong>
                    </p>

                    @if(!$is_available)
                    <p class="room-info-item">
                        Arrival : <strong>
                            {{ !empty($room->arrival_formatted) && trim($room->arrival_formatted) !== '' ? $room->arrival_formatted : (!empty($room->arrival_at) ? @format_datetime($room->arrival_at) : 'N/A') }}
                        </strong>
                    </p>
                    @if(!empty($room->actual_check_in))
                    <p class="room-info-item">
                        Checked In : <strong>{{ $room->actual_check_in }}</strong>
                    </p>
                    @endif
                    <p class="room-info-item room-info-departure">
                        Departure : <strong>
                            {{ !empty($room->departure_formatted) && trim($room->departure_formatted) !== '' ? $room->departure_formatted : (!empty($room->departure_at) ? @format_datetime($room->departure_at) : 'None') }}
                        </strong>
                    </p>

                    @if($room->arrival_at && $room->departure_at)
                        <div class="room-timer"
                            data-arrival="{{ $room->arrival_at }}"
                            data-departure="{{ $room->departure_at }}"
                            data-checked-in="{{ $room->is_checked_in ? 1 : 0 }}"
                            data-server-time="{{ \Carbon\Carbon::now()->toIso8601String() }}">
                            {{ $room->time_left_human ?? '—' }}
                        </div>
                    @else
                        <div class="room-timer-empty">—</div>
                    @endif
                    @else
                    <p class="room-info-item room-info-status">
                        Status : <strong>Ready for Guest</strong>
                    </p>
                    <div class="room-timer-empty">—</div>
                    @endif

                    <div class="room-actions">
                        <button type="button" class="btn-room-status">
                            {{ $status_label }}
                        </button>
                        @if(!empty($room->booking_id))
                        <a href="{{ route('hms.booking.receipt', ['id' => $room->booking_id]) }}"
                           class="js-generate-receipt btn-generate-receipt"
                           data-id="{{ $room->booking_id }}">
                            Generate Receipt
                        </a>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div class="room-empty-col">
            <div class="alert alert-info">No rooms found for this business.</div>
        </div>
        @endforelse
    </div>

    @endcomponent

</section>
@stop

@section('css')
<style>
/* Room Grid & Layout */
.room-grid {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -8px;
}

.room-col {
    width: 25%;
    padding: 0 8px 16px 8px;
    box-sizing: border-box;
}

.room-empty-col {
    width: 100%;
    padding: 0 8px;
}

/* Room Card */
.room-card {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
    min-height: 270px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.room-card-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
}

/* Colored Overlays by Status */
.room-card-overlay {
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
    border-radius: 6px;
    z-index: 1;
}

.room-card.is-occupied .room-card-overlay {
    background: rgba(160, 20, 20, 0.78);
}

.room-card.is-booked .room-card-overlay {
    background: rgba(160, 110, 0, 0.78);
}

.room-card.is-available .room-card-overlay {
    background: rgba(20, 110, 40, 0.72);
}

/* Card Body & Typography */
.room-card-body {
    position: relative;
    z-index: 2;
    padding: 20px 14px 16px 14px;
    text-align: center;
    color: #fff;
    width: 100%;
}

.room-floor-label {
    font-size: 11px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.80);
    margin: 0;
    letter-spacing: 1px;
}

.room-floor-name {
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 8px 0;
    letter-spacing: 0.5px;
}

.room-number {
    font-size: 19px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 8px 0;
}

.room-info-item {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.90);
    margin: 0 0 4px 0;
}

.room-info-item strong {
    color: #fff;
}

.room-info-departure {
    margin-bottom: 6px;
}

.room-info-status {
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 8px;
}

/* Countdown Timer */
.room-timer,
.room-timer-empty {
    font-size: 17px;
    font-weight: 800;
    color: #00e5ff;
    margin-bottom: 12px;
}

/* Actions & Buttons */
.room-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-room-status {
    padding: 9px 14px;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    border: none;
    border-radius: 5px;
    letter-spacing: 0.8px;
    cursor: default;
}

.room-card.is-occupied .btn-room-status {
    background: #c0392b;
}

.room-card.is-booked .btn-room-status {
    background: #e6a817;
}

.room-card.is-available .btn-room-status {
    background: #27ae60;
}

.btn-generate-receipt {
    padding: 9px 14px;
    background: #e6a817;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    border: none;
    border-radius: 5px;
    letter-spacing: 0.8px;
    text-decoration: none;
    display: inline-block;
    transition: opacity 0.2s ease;
}

.btn-generate-receipt:hover,
.btn-generate-receipt:focus {
    color: #fff;
    opacity: 0.9;
    text-decoration: none;
}

/* Responsive Breakpoints */
@media (max-width: 992px) {
    .room-col {
        width: 50% !important;
    }
}

@media (max-width: 576px) {
    .room-col {
        width: 100% !important;
    }
}
</style>
@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function formatDiff(ms) {
        if (ms <= 0) return '0s';
        const total = Math.floor(ms / 1000);
        const days  = Math.floor(total / 86400);
        const hrs   = Math.floor((total % 86400) / 3600);
        const mins  = Math.floor((total % 3600) / 60);
        const secs  = total % 60;
        let str = '';
        if (days > 0) str += days + 'd ';
        str += hrs + 'h ' + String(mins).padStart(2, '0') + 'm ' + String(secs).padStart(2, '0') + 's';
        return str;
    }

    const timers = document.querySelectorAll('.room-timer[data-arrival][data-departure]');
    if (!timers.length) return;

    // Calculate time offset between server and client device clock
    let timeOffset = 0;
    const firstTimer = timers[0];
    const serverTimeStr = firstTimer ? firstTimer.getAttribute('data-server-time') : null;
    if (serverTimeStr) {
        const serverTime = new Date(serverTimeStr);
        if (!isNaN(serverTime.getTime())) {
            timeOffset = serverTime.getTime() - Date.now();
        }
    }

    function tick() {
        const now = new Date(Date.now() + timeOffset);
        timers.forEach(function (el) {
            const arr = el.getAttribute('data-arrival');
            const dep = el.getAttribute('data-departure');
            const isCheckedIn = el.getAttribute('data-checked-in') === '1';

            if (!arr || !dep || arr === 'null' || dep === 'null') {
                el.textContent = '—';
                return;
            }

            const arrivalTime   = new Date(arr);
            const departureTime = new Date(dep);

            if (isNaN(arrivalTime.getTime()) || isNaN(departureTime.getTime())) {
                el.textContent = '—';
                return;
            }

            if (isCheckedIn) {
                // Occupied / Checked-in: countdown to departure
                if (now < departureTime) {
                    el.style.color = '#00e5ff';
                    el.textContent = formatDiff(departureTime - now);
                } else {
                    el.style.color = '#ff6b6b';
                    el.textContent = 'Overdue (' + formatDiff(now - departureTime) + ' ago)';
                }
            } else {
                // Booked but not checked in yet
                if (now < arrivalTime) {
                    el.style.color = '#ffd700';
                    el.textContent = 'Starts in ' + formatDiff(arrivalTime - now);
                } else if (now < departureTime) {
                    el.style.color = '#ffa500';
                    el.textContent = 'Awaiting Check-in';
                } else {
                    el.style.color = '#ff6b6b';
                    el.textContent = 'Booking Expired';
                }
            }
        });
    }

    tick();
    setInterval(tick, 1000);
});
</script>

<script>
function openPrintWindowWithHtml(html) {
    var frame = document.createElement('iframe');
    frame.style.position = 'fixed';
    frame.style.right = '0';
    frame.style.bottom = '0';
    frame.style.width = '0';
    frame.style.height = '0';
    frame.style.border = '0';
    document.body.appendChild(frame);
    var doc = frame.contentWindow || frame.contentDocument;
    if (doc.document) doc = doc.document;
    doc.open(); doc.write(html); doc.close();
    setTimeout(function(){
        try { (frame.contentWindow || frame).focus(); (frame.contentWindow || frame).print(); } catch(e) {}
        setTimeout(function(){ document.body.removeChild(frame); }, 1000);
    }, 250);
}

function triggerReceiptPrint(id) {
    $.ajax({
        url: "{{ url('/hms/booking') }}/" + id + "/receipt",
        data: { ajax: 1 },
        dataType: 'json',
        success: function(res) {
            var html = res.html || '';
            if (html) { openPrintWindowWithHtml(html); }
        }
    });
}

$(document).on('click', '.js-generate-receipt', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    if (!id) {
        var m = ($(this).attr('href') || '').match(/booking\/(\d+)\/receipt/);
        id = m ? m[1] : null;
    }
    if (id) { triggerReceiptPrint(id); }
});
</script>
@endsection
