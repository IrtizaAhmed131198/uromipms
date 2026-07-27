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

    <div style="display:flex; flex-wrap:wrap; margin: 0 -8px;">
        @forelse($rooms as $room)
        <div style="width:25%; padding: 0 8px 16px 8px; box-sizing:border-box;">

            {{--
                CASE 1: Occupied  (checked_in=1, check_out=null) → RED
                CASE 2: Booked    (is_booked=1, not checked in)  → YELLOW
                CASE 3: Available (is_booked=0)                  → GREEN
            --}}

            @php
                $is_occupied  = !empty($room->is_booked) && !empty($room->is_checked_in);
                $is_booked_only = !empty($room->is_booked) && empty($room->is_checked_in);
                $is_available = empty($room->is_booked);

                if ($is_occupied) {
                    $overlay_color = 'rgba(160, 20, 20, 0.78)';   // red
                    $status_label  = 'Occupied';
                    $badge_bg      = '#c0392b';
                } elseif ($is_booked_only) {
                    $overlay_color = 'rgba(160, 110, 0, 0.78)';   // yellow/amber
                    $status_label  = 'Booked';
                    $badge_bg      = '#e6a817';
                } else {
                    $overlay_color = 'rgba(20, 110, 40, 0.72)';   // green
                    $status_label  = 'Available';
                    $badge_bg      = '#27ae60';
                }
            @endphp

            <div style="
                position: relative;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 16px rgba(0,0,0,0.4);
                min-height: 270px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            ">
                {{-- BG Image --}}
                <img src="{{ $room->image_url }}"
                    style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:0;"
                    alt="Room image">

                {{-- Colored overlay --}}
                <div style="
                    position:absolute;
                    top: 10px; left: 10px; right: 10px; bottom: 10px;
                    background: {{ $overlay_color }};
                    border-radius: 6px;
                    z-index:1;
                "></div>

                <div style="position:relative;z-index:2;padding:20px 14px 16px 14px;text-align:center;color:#fff;width:100%;">
                    <p style="font-size:11px;font-weight:500;color:rgba(255,255,255,0.80);margin:0 0 0 0;letter-spacing:1px;">Floor Name</p>
                    <h4 style="font-size:15px;font-weight:800;color:#fff;margin:0 0 8px 0;letter-spacing:0.5px;">
                        {{ strtoupper($room->floor_name === null ? 'GROUND' : $room->floor_name) }}
                    </h4>
                    <h3 style="font-size:19px;font-weight:800;color:#fff;margin:0 0 8px 0;">
                        Room No. {{ $room->room_number ?? $room->id }}
                    </h3>
                    <p style="font-size:12px;color:rgba(255,255,255,0.90);margin:0 0 2px 0;">
                        Room Type : <strong style="color:#fff;">{{ $room->room_type ?? 'Standard' }}</strong>
                    </p>

                    @if(!$is_available)
                    <p style="font-size:12px;color:rgba(255,255,255,0.90);margin:0 0 4px 0;">
                        Arrival : <strong style="color:#fff;">
                            {{ $room->arrival_at ? \Carbon\Carbon::parse($room->arrival_at)->format('d M Y, H:i') : 'N/A' }}
                        </strong>
                    </p>
                    <p style="font-size:12px;color:rgba(255,255,255,0.90);margin:0 0 8px 0;">
                        Check Out : <strong style="color:#fff;">
                            {{ $room->checkout_at ? \Carbon\Carbon::parse($room->checkout_at)->format('d M Y, H:i') : 'None' }}
                        </strong>
                    </p>

                    @if($room->arrival_at && $room->checkout_at)
                        <div class="room-timer"
                            data-arrival="{{ $room->arrival_at }}"
                            data-departure="{{ $room->checkout_at }}"
                            style="font-size:17px;font-weight:800;color:#00e5ff;margin-bottom:12px;">
                            {{ $room->time_left_human ?? '—' }}
                        </div>
                    @else
                        <div style="font-size:17px;font-weight:800;color:#00e5ff;margin-bottom:12px;">—</div>
                    @endif
                    @else
                    <p style="font-size:12px;color:rgba(255,255,255,0.85);margin:0 0 8px 0;">
                        Check Out : <strong style="color:#fff;">None</strong>
                    </p>
                    <div style="font-size:19px;font-weight:800;color:#00e5ff;margin-bottom:12px;">—</div>
                    @endif

                    <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                        <button style="padding:9px 14px;background:{{ $badge_bg }};color:#fff;font-weight:700;font-size:13px;border:none;border-radius:5px;letter-spacing:0.8px;cursor:default;">
                            {{ $status_label }}
                        </button>
                        @if(!empty($room->booking_id))
                        <a href="{{ route('hms.booking.receipt', ['id' => $room->booking_id]) }}"
                           class="js-generate-receipt"
                           data-id="{{ $room->booking_id }}"
                           style="padding:9px 14px;background:#e6a817;color:#fff;font-weight:700;font-size:13px;border:none;border-radius:5px;letter-spacing:0.8px;text-decoration:none;display:inline-block;">
                            Generate Receipt
                        </a>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div style="width:100%;padding:0 8px;">
            <div class="alert alert-info">No rooms found for this business.</div>
        </div>
        @endforelse
    </div>

    @endcomponent

</section>
@stop

@section('css')
<style>
@media (max-width: 992px) {
    .room-col { width: 50% !important; }
}
@media (max-width: 576px) {
    .room-col { width: 100% !important; }
}
</style>
@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function formatDiff(ms) {
        if (ms <= 0) return 'Expired';
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

    function tick() {
        const now = new Date();
        timers.forEach(function (el) {
            const arr = el.getAttribute('data-arrival');
            const dep = el.getAttribute('data-departure');

            if (!arr || !dep || arr === 'null' || dep === 'null') {
                el.textContent = '—';
                return;
            }

            const arrivalTime   = new Date(arr);
            const departureTime = new Date(dep);

            if (isNaN(arrivalTime) || isNaN(departureTime)) {
                el.textContent = '—';
                return;
            }

            // Before arrival: show "Starts in X"
            if (now < arrivalTime) {
                el.style.color = '#ffd700';
                el.textContent = 'Starts in ' + formatDiff(arrivalTime - now);
                return;
            }

            // After arrival, before departure: countdown to checkout
            el.style.color = '#00e5ff';
            el.textContent = formatDiff(departureTime - now);
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
