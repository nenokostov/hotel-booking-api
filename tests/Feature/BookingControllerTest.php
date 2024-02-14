<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $plainTextToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->plainTextToken = $plainTextToken = Str::random(80);

        $this->user->tokens()->create([
            'name' => 'TestToken',
            'token' => hash('sha256', $this->plainTextToken),
            'abilities' => ['*'],
        ]);
    }

        /**
     * @dataProvider dataThatShouldFail
     */
    public function testValidationWillFail(array $requestData): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/bookings', $requestData);

        $response->assertStatus(400);
    }

    public function testUnauthenticatedUserCanNotCreateABooking()
    {
        $room = Room::factory()->create();
        $customer = Customer::factory()->create();

        $bookingData = [
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'check_in_date' => '2024-01-31',
            'check_out_date' => '2024-02-02',
            'total_price' => 200.00,
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotUpdateABooking()
    {
        $booking = Booking::factory()->create();

        $updatedBookingData = [
            'room_id' => $booking->room_id,
            'customer_id' => $booking->customer_id,
            'check_in_date' => '2024-02-01',
            'check_out_date' => '2024-02-04',
            'total_price' => 300.00,
        ];

        $response = $this->putJson('/api/bookings/'. $booking->id, $updatedBookingData);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotDeleteABooking()
    {
        $booking = Booking::factory()->create();

        $response = $this->deleteJson('/api/bookings/'. $booking->id);

        $response->assertStatus(401);
    }

    public function testTryToUpdateABookingWithoutBookingId()
    {
        $booking = Booking::factory()->create();

        $updatedBookingData = [
            'room_id' => $booking->room_id,
            'customer_id' => $booking->customer_id,
            'check_in_date' => '2024-02-01',
            'check_out_date' => '2024-02-04',
            'total_price' => 300.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/bookings/', $updatedBookingData);

        $response->assertStatus(405);
    }

    public function testTryToUpdateABookingWithNoExistingBookingId()
    {
        $booking = Booking::factory()->create();

        $updatedBookingData = [
            'check_in_date' => '2024-02-01',
            'check_out_date' => '2024-02-04',
            'total_price' => 300.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/bookings/1000000000', $updatedBookingData);

        $response->assertStatus(404);
    }

    public function testTryToDeleteABookingWithoutBookingId()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/bookings/');

        $response->assertStatus(405);
    }

    public function testTryToDeleteABookingWithNoExistingBookingId()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/bookings/1000000000');

        $response->assertStatus(404);
    }

    public function testGetASingleBooking()
    {
        $room = Room::factory()->create();
        $customer = Customer::factory()->create();

        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
        ]);

        $response = $this->get('/api/bookings/'. $booking->id);

        $response->assertStatus(200);
        $response->assertJson(['booking' => [
            'id' => $booking->id,
            'room_id' => $room->id,
            'customer_id' => $customer->id,
        ]]);
    }

    public function testListBookings()
    {
        $room = Room::factory()->create();
        $customer = Customer::factory()->create();

        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
        ]);

        $room2 = Room::factory()->create();
        $customer2 = Customer::factory()->create();

        $booking2 = Booking::factory()->create([
            'room_id' => $room2->id,
            'customer_id' => $customer2->id,
        ]);

        $response = $this->get('/api/bookings');

        $response->assertStatus(200);

        $bookingsJson = $response->json();
        $this->assertCount(2, $bookingsJson['bookings']);
    }

    public function testCreateABooking()
    {
        $room = Room::factory()->create();
        $customer = Customer::factory()->create();

        $bookingData = [
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'check_in_date' => '2024-01-31',
            'check_out_date' => '2024-02-02',
            'total_price' => 200.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/bookings', $bookingData);

        $response->assertStatus(200);
        $response->assertJsonFragment($bookingData);
    }

    public function testUpdateABooking()
    {
        $booking = Booking::factory()->create();

        $updatedBookingData = [
            'check_in_date' => '2024-02-01',
            'check_out_date' => '2024-02-04',
            'total_price' => 300.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/bookings/'. $booking->id, $updatedBookingData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updatedBookingData);
    }

    public function testDeleteABooking()
    {
        $booking = Booking::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/bookings/'. $booking->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function testCompleteBookings()
    {
        $room = Room::factory()->create();
        $customer = Customer::factory()->create();

        $bookingData = [
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'check_in_date' => '2024-01-31',
            'check_out_date' => '2024-02-02',
            'total_price' => 200.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/bookings', $bookingData);

        $response->assertStatus(200);
        $response->assertJsonFragment($bookingData);

        $booking = $response->json()['booking'];
        $bookingId  = $booking['id'];

        $updatedBookingData = [
            'total_price' => 400.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/bookings/'. $bookingId, $updatedBookingData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updatedBookingData);

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/bookings/'. $bookingId);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('bookings', ['id' => $bookingId]);
    }

    public static function dataThatShouldFail(): array
    {
        $roomId = rand();
        $customerId = rand();
        $checkInDate = '2024-01-31';
        $checkOutDate = '2024-02-02';
        $totalPrice = 200.00;

        return [
            'no_room_id' => [
                [
                    'customer_id' => $customerId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'total_price' => $totalPrice,
                ],
            ],
            'no_customer_id' => [
                [
                    'room_id' => $roomId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'total_price' => $totalPrice,
                ],
            ],
            'no_check_in_date' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => $customerId,
                    'check_out_date' => $checkOutDate,
                    'total_price' => $totalPrice,
                ],
            ],
            'no_check_out_date' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => $customerId,
                    'check_in_date' => $checkInDate,
                    'total_price' => $totalPrice,
                ],
            ],
            'no_total_price' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => $customerId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ],
            ],
            'invalid_room_id' => [
                [
                    'room_id' => 'rooom_id',
                    'customer_id' => $customerId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'total_price' => $totalPrice,
                ],
            ],
            'invalid_customer_id' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => 'customer_id',
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'total_price' => $totalPrice,
                ],
            ],
            'invalid_check_in_date' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => $customerId,
                    'check_in_date' => 'check_in_date',
                    'check_out_date' => $checkOutDate,
                    'total_price' => $totalPrice,
                ],
            ],
            'invalid_check_out_date' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => $customerId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => 'check_out_dates',
                    'total_price' => $totalPrice,
                ],
            ],
            'invalid_check_out_date_before_check_in_date' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => $customerId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => '2024-01-28',
                    'total_price' => $totalPrice,
                ],
            ],
            'invalid_total_price' => [
                [
                    'room_id' => $roomId,
                    'customer_id' => $customerId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'total_price' => 'total_price',
                ],
            ],
        ];
    }
}
