<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public static $rules = [
        'booking_id' => 'required|exists:bookings,id',
        'amount' => 'required|numeric',
        'payment_date' => 'required|date',
        'status' => 'required|in:completed,pending,failed',
    ];

    public static $updateRules = [
        'booking_id' => 'sometimes|required|exists:bookings,id',
        'amount' => 'sometimes|required|numeric',
        'payment_date' => 'sometimes|required|date',
        'status' => 'sometimes|required|in:completed,pending,failed',
    ];

    public static $messages = [
        'booking_id.required' => 'The booking ID is required.',
        'booking_id.exists' => 'The selected booking is invalid.',
        'amount.required' => 'The amount is required.',
        'amount.numeric' => 'The amount must be a number.',
        'payment_date.required' => 'The payment date is required.',
        'payment_date.date' => 'The payment date must be a valid date.',
        'status.required' => 'The status is required.',
        'status.in' => 'The status must be one of: completed, pending, failed.',
    ];

    protected $fillable = ['booking_id', 'amount', 'payment_date', 'status'];

    public function updatePayment(array $attributes) {
        try {
            $this->fill($attributes)->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
