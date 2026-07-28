<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the business that owns the request.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the source location (location fulfilling the request).
     */
    public function sourceLocation()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'source_location_id');
    }

    /**
     * Get the destination location (location making the request).
     */
    public function destinationLocation()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'destination_location_id');
    }

    /**
     * Get the user who requested the stock.
     */
    public function requestedBy()
    {
        return $this->belongsTo(\App\User::class, 'requested_by');
    }

    /**
     * Get the user who approved the stock.
     */
    public function approvedBy()
    {
        return $this->belongsTo(\App\User::class, 'approved_by');
    }

    /**
     * Get the user who accepted the stock.
     */
    public function acceptedBy()
    {
        return $this->belongsTo(\App\User::class, 'accepted_by');
    }

    /**
     * Get the lines for the request.
     */
    public function lines()
    {
        return $this->hasMany(InventoryRequestLine::class);
    }
}
