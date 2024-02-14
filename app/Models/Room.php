<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    public static $rules = [
        'number' => 'required|unique:rooms|numeric',
        'type' => 'required|string|max:255',
        'price_per_night' => 'required|numeric|min:0',
        'status' => 'required|in:available,booked,maintenance',
    ];

    public static $updateRules = [
        'number' => 'sometimes|required|unique:rooms|numeric',
        'type' => 'sometimes|required|string|max:255',
        'price_per_night' => 'sometimes|required|numeric|min:0',
        'status' => 'sometimes|required|in:available,booked,maintenance',
    ];

    public static $messages = [
        'number.required' => 'The room number is required.',
        'number.unique' => 'The room number must be unique.',
        'number.numeric' => 'The room number must be a numeric value.',
        'type.required' => 'The room type is required.',
        'type.string' => 'The room type must be a string.',
        'type.max' => 'The room type may not be greater than 255 characters.',
        'price_per_night.required' => 'The price per night is required.',
        'price_per_night.numeric' => 'The price per night must be a numeric value.',
        'price_per_night.min' => 'The price per night must be at least :min.',
        'status.required' => 'The room status is required.',
        'status.in' => 'Invalid room status.',
    ];

    protected $fillable = ['number', 'type', 'price_per_night', 'status'];

    public function updateRoom(array $attributes) {
        try {
            $this->fill($attributes)->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }
}
