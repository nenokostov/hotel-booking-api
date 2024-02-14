<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

 /**
 * @OA\Schema(
 *     schema="CustomerPOST",
 *     title="Customer for POST",
 *     description="Customer object",
 *     required={"name", "email", "phone_number"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the customer",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email of the customer",
 *         example="johnUpdated@example.com"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         description="Phone number of the customer",
 *         example=1234567890
 *     ),
 * )
 *
 * @OA\Schema(
 *     schema="CustomerPUT",
 *     title="Customer for PUT",
 *     description="Customer object",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the customer",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email of the customer",
 *         example="johnUpdated@example.com"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         description="Phone number of the customer",
 *         example=1234567890
 *     ),
 * )
 *
 * @OA\Schema(
 *     schema="CustomerWithId",
 *     title="Customer with ID",
 *     description="Customer object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the customer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the customer",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email of the customer",
 *         example="johnUpdated@example.com"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         description="Phone number of the customer",
 *         example=1234567890
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
class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/customers",
     *     summary="Get all customers",
     *     tags={"Customers"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerWithId")
     *         )
     *     ),
     * )
     *
     */
    public function index()
    {
        $customers = Customer::all();

        return response()->json(['customers' => $customers]);
    }

    /**
     * @OA\Get(
     *     path="/customers/{id}",
     *     summary="Get a customer by ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerWithId")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            return response()->json(['customer' => $customer]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/customers",
     *     summary="Create a new customer",
     *     tags={"Customers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerPOST")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerWithId")
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
            $validatedData = $request->validate(Customer::$rules, Customer::$messages);

            $customer = Customer::create($validatedData);

            return response()->json(['customer' => $customer], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/customers/{id}",
     *     summary="Update a customer by ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerPUT")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerWithId")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate(Customer::$updateRules, Customer::$messages);

            $customer = Customer::findOrFail($id);

            $customer->updateCustomer($validatedData);

            return response()->json(['customer' => $customer], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Customer not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/customers/{id}",
     *     summary="Delete a customer by ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Customer deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $customer->delete();

            return response()->json([], 204);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }
}
