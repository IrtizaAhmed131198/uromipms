<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryRequestLine extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the inventory request that owns the line.
     */
    public function inventoryRequest()
    {
        return $this->belongsTo(InventoryRequest::class);
    }

    /**
     * Get the product for the line.
     */
    public function product()
    {
        return $this->belongsTo(\App\Product::class);
    }

    /**
     * Get the variation for the line.
     */
    public function variation()
    {
        return $this->belongsTo(\App\Variation::class);
    }
}
