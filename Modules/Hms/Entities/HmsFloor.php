<?php

namespace Modules\Hms\Entities;

use Illuminate\Database\Eloquent\Model;

class HmsFloor extends Model
{
    protected $fillable = ['business_id', 'hms_building_id', 'name'];

    public function building()
    {
        return $this->belongsTo(HmsBuilding::class, 'hms_building_id');
    }
}
