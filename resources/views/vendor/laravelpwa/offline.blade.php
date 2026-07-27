<!--@extends('layouts.app')-->

<!--@section('content')-->

<!--    <h1>You are currently not connected to any networks.</h1>-->

<!--@endsection-->


@extends('layouts.app')

@section('content')
<style>
    .offline-container {
        text-align: center;
        padding: 50px 20px;
    }
    .offline-icon {
        font-size: 80px;
        opacity: 0.6;
    }
    .offline-text {
        font-size: 22px;
        font-weight: 600;
        margin-top: 20px;
    }
    .offline-subtext {
        color: #666;
        margin-top: 10px;
        font-size: 16px;
    }
    .offline-btn {
        margin-top: 25px;
        padding: 10px 25px;
        background: #0d6efd;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
    }
</style>

<div class="offline-container">
    <div class="offline-icon">📡</div>
    <div class="offline-text">You're Offline</div>
    <div class="offline-subtext">Please check your internet connection.</div>

    <a href="/" class="offline-btn">Retry</a>
</div>
@endsection
