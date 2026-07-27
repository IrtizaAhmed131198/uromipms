@extends('layouts.auth2')
@section('title', config('app.name', 'ultimatePOS'))
@inject('request', 'Illuminate\Http\Request')
@section('content')
<style>
    .right-col {
        background: linear-gradient(to right, #6366f1, #3b82f6);
        height: 100%;
    }
</style>
<div class="col-md-12 col-sm-12 col-xs-12 right-col tw-pt-20 tw-pb-10 tw-px-5 tw-flex tw-flex-col tw-items-center tw-justify-center tw-bg-blue-500">
    <div class="tw-text-6xl tw-font-extrabold tw-text-center tw-text-white tw-shadow-lg tw-px-4 tw-py-2 tw-bg-blue-700 tw-rounded-md">
        {{ config('app.name', 'UltimatePOS') }}
    </div>
    
    <p class="tw-text-lg tw-font-medium tw-text-center tw-text-white tw-mt-2 tw-shadow-md tw-bg-blue-600 tw-rounded-md tw-px-3 tw-py-1">
        {{ env('APP_TITLE', '') }}
    </p>
    <!-- ✅ PWA Install Button -->
<button id="installAppBtn"
    style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:#0d6efd; color:#fff; padding:12px 25px; border:none; border-radius:10px; font-size:16px; z-index:99999; box-shadow:0 4px 10px rgba(0,0,0,0.25);">
    Install App
</button>
</div>

@endsection
@section('javascript')
<script type="text/javascript">
    let deferredPrompt;
    
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
    
        const btn = document.getElementById('installAppBtn');
        if (btn) {
            btn.style.display = 'block';
        }
    });
    
    document.getElementById('installAppBtn').addEventListener('click', () => {
        const btn = document.getElementById('installAppBtn');
        btn.style.display = 'none';
    
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt = null;
        }
    });
</script>
@endsection
            