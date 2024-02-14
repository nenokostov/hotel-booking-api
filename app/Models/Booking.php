<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    public static $rules = [
        'room_id' => 'required|exists:rooms,id',
        'customer_id' => 'required|exists:customers,id',
        'check_in_date' => 'required|date',
        'check_out_date' => 'required|date|after:check_in_date',
        'total_price' => 'required|numeric',
    ];

    public static $updateRules = [
        'room_id' => 'sometimes|required|exists:rooms,id',
        'customer_id' => 'sometimes|required|exists:customers,id',
        'check_in_date' => 'sometimes|required|date',
        'check_out_date' => 'sometimes|required|date|after:check_in_date',
        'total_price' => 'sometimes|required|numeric',
    ];

    public static $messages = [
        'room_id.required' => 'The room ID is required.',
        'room_id.exists' => 'The selected room is invalid.',
        'customer_id.required' => 'The customer ID is required.',
        'customer_id.exists' => 'The selected customer is invalid.',
        'check_in_date.required' => 'The check-in date is required.',
        'check_in_date.date' => 'The check-in date must be a valid date.',
        'check_out_date.required' => 'The check-out date is required.',
        'check_out_date.date' => 'The check-out date must be a valid date.',
        'check_out_date.after' => 'The check-out date must be after the check-in date.',
        'total_price.required' => 'The total price is required.',
        'total_price.numeric' => 'The total price must be a number.',
    ];

    protected $fillable = ['room_id', 'customer_id', 'check_in_date', 'check_out_date', 'total_price'];

    public function updateBooking(array $attributes) {
        try {
            $this->fill($attributes)->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
