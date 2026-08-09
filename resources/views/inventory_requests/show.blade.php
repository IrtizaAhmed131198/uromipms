@extends('layouts.app')
@section('title', __('View Inventory Request'))

@section('content')

<section class="content-header">
    <h1>@lang('Inventory Request Details') <small>{{$inventoryRequest->request_number}}</small></h1>
</section>

<section class="content">
    
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                Status: <span class="label bg-blue">{{$inventoryRequest->status}}</span>
            </h3>
        </div>
        <div class="box-body">
            
            <div class="row">
                <div class="col-sm-4">
                    <strong>Source Location:</strong><br>
                    {{$inventoryRequest->sourceLocation->name}}
                </div>
                <div class="col-sm-4">
                    <strong>Destination Location:</strong><br>
                    {{$inventoryRequest->destinationLocation->name}}
                </div>
                <div class="col-sm-4">
                    <strong>Requested By:</strong><br>
                    {{$inventoryRequest->requestedBy->user_full_name}}
                </div>
            </div>
            <br>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity Requested</th>
                            <th>Quantity Approved</th>
                            <th>Quantity Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventoryRequest->lines as $line)
                            <tr>
                                <td>{{$line->product->name}} @if($line->product->type == 'variable') - {{$line->variation->name}} @endif</td>
                                <td>{{@format_quantity($line->quantity_requested)}}</td>
                                <td>{{@format_quantity($line->quantity_approved)}}</td>
                                <td>{{@format_quantity($line->quantity_received)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($inventoryRequest->rejection_reason)
            <div class="row" style="margin-top: 15px;">
                <div class="col-sm-12">
                    <div class="alert alert-danger" style="border-radius: 8px;">
                        <h4 style="margin-top: 0; font-weight: 600;"><i class="fas fa-ban"></i> Rejection Reason</h4>
                        <p style="font-size: 15px; margin-bottom: 0;">{{$inventoryRequest->rejection_reason}}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($inventoryRequest->notes)
            <div class="row">
                <div class="col-sm-12">
                    <strong>Notes:</strong><br>
                    {{$inventoryRequest->notes}}
                </div>
            </div>
            @endif

        </div>
    </div>
</section>

@endsection
