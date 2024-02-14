<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\BookingMade;
use App\Events\BookingCanceled;
use Illuminate\Support\Facades\Event;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="BookingPOST",
 *     title="Booking for POST",
 *     description="Booking object",
 *     required={"room_id", "customer_id", "check_in_date", "check_out_date"},
 *     @OA\Property(
 *         property="room_id",
 *         type="integer",
 *         description="ID of the room booked",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="customer_id",
 *         type="integer",
 *         description="ID of the customer who made the booking",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="check_in_date",
 *         type="string",
 *         format="date",
 *         description="Check-in date for the booking",
 *         example="2024-02-13"
 *     ),
 *     @OA\Property(
 *         property="check_out_date",
 *         type="string",
 *         format="date",
 *         description="Check-out date for the booking",
 *         example="2024-02-15"
 *     ),
 * )
 *
 * @OA\Schema(
 *     schema="BookingPUT",
 *     title="Booking for PUT",
 *     description="Booking object",
 *     @OA\Property(
 *         property="room_id",
 *         type="integer",
 *         description="ID of the room booked",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="customer_id",
 *         type="integer",
 *         description="ID of the customer who made the booking",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="check_in_date",
 *         type="string",
 *         format="date",
 *         description="Check-in date for the booking",
 *         example="2024-02-13"
 *     ),
 *     @OA\Property(
 *         property="check_out_date",
 *         type="string",
 *         format="date",
 *         description="Check-out date for the booking",
 *         example="2024-02-15"
 *     ),
 * )
 *
 * @OA\Schema(
 *     schema="BookingWithId",
 *     title="Booking with ID",
 *     description="Booking object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the booking",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="room_id",
 *         type="integer",
 *         description="ID of the room booked",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="customer_id",
 *         type="integer",
 *         description="ID of the customer who made the booking",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="check_in_date",
 *         type="string",
 *         format="date",
 *         description="Check-in date for the booking",
 *         example="2024-02-13"
 *     ),
 *     @OA\Property(
 *         property="check_out_date",
 *         type="string",
 *         format="date",
 *         description="Check-out date for the booking",
 *         example="2024-02-15"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the booking was created",
 *         example="2024-02-13 12:00:00"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the booking was last updated",
 *         example="2024-02-13 12:00:00"
 *     )
 * )
 */
class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bookings",
     *     summary="Get all bookings",
     *     tags={"Bookings"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/BookingWithId")
     *         )
     *     ),
     * )
     */
    public function index()
    {
        $bookings = Booking::all();

        return response()->json(['bookings' => $bookings]);
    }

    /**
     * @OA\Get(
     *     path="/bookings/{id}",
     *     summary="Get a booking by ID",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the booking",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BookingWithId")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            return response()->json(['booking' => $booking]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/bookings",
     *     summary="Create a new booking",
     *     tags={"Bookings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookingPOST")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/BookingWithId")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(Booking::$rules, Booking::$messages);

            $roomId = $validatedData['room_id'];
            $customerId = $validatedData['customer_id'];

            $room = Room::findOrFail($roomId);

            if ($room->status != 'available') {
                return response()->json(['error' => 'Room not available'], 400);
            }

            $customer = Customer::findOrFail($customerId);

            $booking = Booking::create($validatedData);

            $room->status = 'booked';
            $room->save();

            event(new BookingMade($booking->id));

            return response()->json(['booking' => $booking], 200);

        } catch (ModelNotFoundException $e) {
            if (!isset($room)) {
                return response()->json(['error' => 'Room not found'], 404);
            } elseif (!isset($customer)) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/bookings/{id}",
     *     summary="Update a booking by ID",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the booking",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookingPUT")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/BookingWithId")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate(Booking::$updateRules, Booking::$messages);

            if (!empty($validatedData['room_id'])) {
                $roomId = $validatedData['room_id'];

                $room = Room::findOrFail($roomId);

                if ($room->status != 'available') {
                    return response()->json(['error' => 'Room not available'], 400);
                }
            }

            if (!empty($validatedData['customer_id'])) {
                $customerId = $validatedData['customer_id'];

                $customer = Customer::findOrFail($customerId);
            }

            $booking = Booking::findOrFail($id);

            $oldRoomId = $booking->room_id;

            $booking->updateBooking($validatedData);

            if (isset($roomId) && $oldRoomId != $roomId) {
                $room->status = 'booked';
                $room->save();

                $roomMadeAvailable = Room::findOrFail($oldRoomId);

                $roomMadeAvailable->status = 'available';
                $roomMadeAvailable->save();
            }

            return response()->json(['booking' => $booking], 200);

        } catch (ModelNotFoundException $e) {
            if (!isset($room) && !empty($validatedData['room_id'])) {
                return response()->json(['error' => 'Room not found'], 404);
            } elseif (!isset($customer) && !empty($validatedData['customer_id'])) {
                return response()->json(['error' => 'Customer not found'], 404);
            } elseif (!isset($booking)) {
                return response()->json(['error' => 'Booking not found'], 404);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/bookings/{id}",
     *     summary="Delete a booking by ID",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the booking",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Booking deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            $room = Room::findOrFail($booking->room_id);

            if ($room && $room->status != 'available') {
                $room->status = 'available';
                $room->save();
            }

            $booking->delete();

            event(new BookingCanceled($booking->id));

            return response()->json([], 204);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
    }
}