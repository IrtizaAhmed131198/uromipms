@extends('layouts.auth2')
@section('title', __('lang_v1.login'))
@inject('request', 'Illuminate\Http\Request')
@section('content')
    @php
        $username = old('username');
        $password = null;
        if (config('app.env') == 'demo') {
            $username = 'admin';
            $password = '123456';

            $demo_types = [
                'all_in_one' => 'admin',
                'super_market' => 'admin',
                'pharmacy' => 'admin-pharmacy',
                'electronics' => 'admin-electronics',
                'services' => 'admin-services',
                'restaurant' => 'admin-restaurant',
                'superadmin' => 'superadmin',
                'woocommerce' => 'woocommerce_user',
                'essentials' => 'admin-essentials',
                'manufacturing' => 'manufacturer-demo',
            ];

            if (!empty($_GET['demo_type']) && array_key_exists($_GET['demo_type'], $demo_types)) {
                $username = $demo_types[$_GET['demo_type']];
            }
        }
    @endphp
    @php
        $login_background_image = \App\System::getProperty('login_background_image');
        $bg_url = !empty($login_background_image) ? asset('uploads/login_backgrounds/' . $login_background_image) : null;
        $default_bg = asset('img/login-bg-default.png');
        if (empty($bg_url)) {
            $bg_url = $default_bg;
        }
    @endphp

    <style>
        .container-fluid {
            min-height: 100vh;
            background: url('{{ $bg_url }}') center center / cover no-repeat fixed !important;
        }
    </style>

    <div class="row">
        <div class="col-md-4">
        </div>

        <div class="col-md-4">
            <div class="tw-p-5 md:tw-p-6 tw-mb-4 tw-rounded-2xl tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-ring-1 tw-ring-gray-200">
                <div class="tw-flex tw-flex-col tw-gap-4 tw-dw-rounded-box tw-dw-p-6 tw-dw-max-w-md">
                    <div class="tw-flex tw-items-center tw-flex-col">
                        <h1 class="tw-text-lg md:tw-text-xl tw-font-semibold tw-text-[#1e1e1e]">
                            @lang('lang_v1.welcome_back')
                        </h1>
                        <h2 class="tw-text-sm tw-font-medium tw-text-gray-500">
                            @lang('lang_v1.login_to_your') {{ config('app.name', 'ultimatePOS') }}
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        {{ csrf_field() }}
                        <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
                            <label class="tw-dw-form-control">
                                <div class="tw-dw-label">
                                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">@lang('Username')</span>
                                </div>
                                <input
                                    class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 tw-bg-transparent tw-rounded-lg tw-px-3 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium"
                                    name="username" required autofocus placeholder="@lang('lang_v1.username')"
                                    id="username" type="text" value="{{ $username }}" />
                            </label>
                        </div>

                        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                            <label class="tw-dw-form-control">
                                <div class="tw-dw-label">
                                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">@lang('Password')</span>
                                </div>

                                <input
                                    class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 tw-bg-transparent tw-rounded-lg tw-px-3 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium"
                                    id="password" type="password" name="password" required
                                    placeholder="@lang('lang_v1.password')" />
                            </label>
                        </div>

                        <button type="submit"
                            class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-w-full tw-max-w-full mt-2">
                            @lang('lang_v1.login')
                        </button>
                    </form>

                    <!-- ✅ ✅ INSTALL APP BUTTON ADDED HERE -->
                    <button id="installAppBtn"
                        style="display:none; margin-top:15px; background:#0d6efd; color:#fff; padding:10px 20px; border:none; border-radius:8px; width:100%; font-size:16px;">
                        Install App
                    </button>
                    <!-- ✅ ✅ END -->

                    <div class="tw-flex tw-items-center tw-flex-col tw-mt-2">
                        <a href="https://edrictech.com.ng" target="_blank"
                            class="tw-text-sm tw-font-medium tw-text-gray-500">Powered By EdricTech</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4"></div>
    </div>

@stop

@section('javascript')
<script type="text/javascript">

// ✅ ✅ PWA INSTALL LOGIC
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
