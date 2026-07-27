<?php
namespace Modules\Hms\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HmsStayExtension extends Model
{
    use HasFactory;

    protected $table = 'hms_stay_extensions';
    protected $guarded = ['id'];

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }
}
