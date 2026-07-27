<?php

namespace Modules\Hms\Entities;

use Illuminate\Database\Eloquent\Model;

class HmsBuilding extends Model
{
    protected $fillable = ['business_id', 'name', 'location'];

    public function floors()
    {
        return $this->hasMany(HmsFloor::class);
    }
}
