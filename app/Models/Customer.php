<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    public static $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:customers|string',
        'phone_number' => 'required|string|max:15',
    ];

    public static $updateRules = [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:customers|string',
        'phone_number' => 'sometimes|required|string|max:15',
    ];

    public static $messages = [
        'name.required' => 'The customer name is required.',
        'name.string' => 'The customer name must be a string.',
        'name.max' => 'The customer name may not be greater than 255 characters.',
        'email.required' => 'The customer email is required.',
        'email.unique' => 'The customer email must be unique.',
        'email.string' => 'The customer email must be a string.',
        'phone_number.required' => 'The customer phone number is required.',
        'phone_number.string' => 'The customer phone number must be a string.',
        'phone_number.max' => 'The customer phone number may not be greater than 15 characters.',
    ];

    protected $fillable = ['name', 'email', 'phone_number'];

    public function updateCustomer(array $attributes) {
        try {
            $this->fill($attributes)->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
