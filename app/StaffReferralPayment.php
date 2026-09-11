<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaffReferralPayment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'staff_referral_payments';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the user (staff) who received the payment.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    /**
     * Get the admin who created the payment.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Get the business associated with the payment.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    /**
     * Get the payment account.
     */
    public function account()
    {
        return $this->belongsTo(\App\Account::class, 'account_id');
    }
}
