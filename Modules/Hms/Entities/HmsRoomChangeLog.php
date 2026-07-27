<?php

namespace Modules\Hms\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HmsRoomChangeLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'hms_room_change_logs';
}
