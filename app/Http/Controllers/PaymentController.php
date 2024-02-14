<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

/**
 *  @OA\Schema(
 *     schema="PaymentPOST",
 *     title="Payment for POST",
 *     description="Payment object",
 *     required={"booking_id", "amount", "payment_date", "status"},
 *     @OA\Property(
 *         property="booking_id",
 *         type="integer",
 *         description="ID of the booking associated with the payment",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="number",
 *         format="float",
 *         description="Amount of the payment",
 *         example=100.00
 *     ),
 *     @OA\Property(
 *         property="payment_date",
 *         type="string",
 *         description="Date of the payment",
 *         example="2024-02-13"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"completed","pending","failed"},
 *         description="Status of the payment"
 *     ),
 * )
 *
 *  @OA\Schema(
 *     schema="PaymentPUT",
 *     title="Payment for PUT",
 *     description="Payment object",
 *     @OA\Property(
 *         property="booking_id",
 *         type="integer",
 *         description="ID of the booking associated with the payment",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="number",
 *         format="float",
 *         description="Amount of the payment",
 *         example=100.00
 *     ),
 *     @OA\Property(
 *         property="payment_date",
 *         type="string",
 *         description="Date of the payment",
 *         example="2024-02-13"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"completed","pending","failed"},
 *         description="Status of the payment"
 *     ),
 * )
*
 * @OA\Schema(
 *     schema="PaymentWithId",
 *     title="Payment with ID",
 *     description="Payment object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the payment",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="booking_id",
 *         type="integer",
 *         description="ID of the booking associated with the payment",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="number",
 *         format="float",
 *         description="Amount of the payment",
 *         example=100.00
 *     ),
 *     @OA\Property(
 *         property="payment_date",
 *         type="string",
 *         description="Date of the payment",
 *         example="2024-02-13"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"completed","pending","failed"},
 *         description="Status of the payment"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the payment was created",
 *         example="2024-02-13 12:00:00"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the payment was last updated",
 *         example="2024-02-13 12:00:00"
 *     )
 * )
 */
class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/payments",
     *     summary="Get all payments",
     *     tags={"Payments"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PaymentWithId")
     *         )
     *     ),
     * )
     */
    public function index()
    {
        $payments = Payment::all();

        return response()->json(['payments' => $payments]);
    }

    /**
     * @OA\Get(
     *     path="/payments/{id}",
     *     summary="Get a payment by ID",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentWithId")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $payment = Payment::findOrFail($id);

            return response()->json(['payment' => $payment]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/payments",
     *     summary="Create a new payment",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PaymentPOST")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentWithId")
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
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(Payment::$rules, Payment::$messages);

            $bookingId = $validatedData['booking_id'];

            Booking::findOrFail($bookingId);

            $payment = Payment::create($validatedData);

            return response()->json(['payment' => $payment], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Booking not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/payments/{id}",
     *     summary="Update a payment by ID",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PaymentPUT")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentWithId")
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
            $validatedData = $request->validate(Payment::$updateRules, Payment::$messages);

            if (!empty($validatedData['booking_id'])) {
                $bookingId = $validatedData['booking_id'];

                Booking::findOrFail($bookingId);
            }

            $payment = Payment::findOrFail($id);

            $payment->updatePayment($validatedData);

            return response()->json(['payment' => $payment], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Booking not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/payments/{id}",
     *     summary="Delete a payment by ID",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Payment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $payment = Payment::findOrFail($id);

            $payment->delete();

            return response()->json([], 204);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
    }
}

