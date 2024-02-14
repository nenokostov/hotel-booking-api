<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

 /**
 * @OA\Info(
 *     title="Hotel Booking API",
 *     version="1.0.0",
 *     description="API endpoints for managing hotel bookings",
 *     @OA\Contact(
 *         email="contact@example.com"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="RoomPOST",
 *     title="Room for POST",
 *     description="Room object",
 *     required={"number", "type", "price_per_night", "status"},
 *     @OA\Property(
 *         property="number",
 *         type="integer",
 *         format="int64",
 *         description="Number of the room",
 *         example=101
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type of the room",
 *         example="Single"
 *     ),
 *     @OA\Property(
 *         property="price_per_night",
 *         type="number",
 *         format="double",
 *         description="Price per night for the room",
 *         example=100.00
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"available", "booked", "maintenance"},
 *         description="Status of the room"
 *     ),
 * )
 * @OA\Schema(
 *     schema="RoomPUT",
 *     title="Room for PUT",
 *     description="Room object",
 *     @OA\Property(
 *         property="number",
 *         type="integer",
 *         format="int64",
 *         description="Number of the room",
 *         example=101
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type of the room",
 *         example="Single"
 *     ),
 *     @OA\Property(
 *         property="price_per_night",
 *         type="number",
 *         format="double",
 *         description="Price per night for the room",
 *         example=100.00
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"available", "booked", "maintenance"},
 *         description="Status of the room"
 *     ),
 * )
 *
 * @OA\Schema(
 *     schema="RoomWithId",
 *     title="Room with ID",
 *     description="Room object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the room",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="number",
 *         type="integer",
 *         format="int64",
 *         description="Number of the room",
 *         example=101
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type of the room",
 *         example="Single"
 *     ),
 *     @OA\Property(
 *         property="price_per_night",
 *         type="number",
 *         format="double",
 *         description="Price per night for the room",
 *         example=100.00
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"available", "booked", "maintenance"},
 *         description="Status of the room"
 *     ),
 * )
 */
class RoomController extends Controller
{
     /**
     * @OA\Get(
     *     path="/rooms",
     *     summary="Get all rooms",
     *     tags={"Rooms"},
     *     operationId="getRooms",
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RoomWithId")
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $rooms = Room::all();

        return response()->json(['rooms' => $rooms]);
    }

    /**
     * @OA\Get(
     *     path="/rooms/{id}",
     *     summary="Get room by ID",
     *     tags={"Rooms"},
     *     operationId="getRoomById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of room to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/RoomWithId"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found",
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $room = Room::findOrFail($id);

            return response()->json(['room' => $room]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Room not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/rooms",
     *     summary="Create a new room",
     *     tags={"Rooms"},
     *     operationId="createRoom",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RoomPOST")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RoomWithId"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(Room::$rules, Room::$messages);

            $room = Room::create($validatedData);

            return response()->json(['room' => $room], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/rooms/{id}",
     *     summary="Update a room",
     *     tags={"Rooms"},
     *     operationId="updateRoom",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of room to update",
     *         required=true,
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RoomPUT")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RoomWithId"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found",
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate(Room::$updateRules, Room::$messages);

            $room = Room::findOrFail($id);

            $room->updateRoom($validatedData);

            return response()->json(['room' => $room], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Room not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/rooms/{id}",
     *     summary="Delete a room",
     *     tags={"Rooms"},
     *     operationId="deleteRoom",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of room to delete",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Room deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found",
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $room = Room::findOrFail($id);

            $room->delete();

            return response()->json([], 204);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Room not found'], 404);
        }
    }
}
