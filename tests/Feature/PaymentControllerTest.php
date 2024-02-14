<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
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
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/payments', $requestData);

        $response->assertStatus(400);
    }

    public function testUnauthenticatedUserCanNotCreateAPayment()
    {
        $booking = Booking::factory()->create();

        $data = [
            'booking_id' => $booking->id,
            'amount' => 100.50,
            'payment_date' => now()->toDateString(),
            'status' => 'completed',
        ];

        $response = $this->post('/api/payments', $data);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotUpdateAPayment()
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create();

        $newData = [
            'booking_id' => $booking->id,
            'amount' => 150.75,
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
        ];

        $response = $this->put('/api/payments/'. $payment->id, $newData);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotDeleteAPayment()
    {
        $payment = Payment::factory()->create();

        $response = $this->delete('/api/payments/'. $payment->id);

        $response->assertStatus(401);
    }

    public function testTryToUpdateAPaymentWithoutPaymentId()
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create();

        $paymentData = [
            'booking_id' => $booking->id,
            'amount' => 150.75,
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->put('/api/payments/', $paymentData);

        $response->assertStatus(405);
    }

    public function testTryToUpdateAPaymentWithNoExistingPaymentId()
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create();

        $paymentData = [
            'booking_id' => $booking->id,
            'amount' => 150.75,
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->put('/api/payments/1000000000', $paymentData);

        $response->assertStatus(404);
    }

    public function testTryToDeleteAPaymentWithoutPaymentId()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->delete('/api/payments/');

        $response->assertStatus(405);
    }

    public function testTryToDeleteAPaymentWithNoExistingPaymentId()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->delete('/api/payments/1000000000');

        $response->assertStatus(404);
    }

    public function testGetASinglePayment()
    {
        $payment = Payment::factory()->create();

        $response = $this->get('/api/payments/'. $payment->id);

        $response->assertStatus(200);
        $response->assertJsonStructure(['payment']);
    }

    public function testListPayments()
    {
        Payment::factory(3)->create();

        $response = $this->get('/api/payments');

        $response->assertStatus(200);

        $paymentsJson = $response->json();
        $this->assertCount(3, $paymentsJson['payments']);
    }

    public function testCreateAPayment()
    {
        $booking = Booking::factory()->create();

        $data = [
            'booking_id' => $booking->id,
            'amount' => 100.50,
            'payment_date' => now()->toDateString(),
            'status' => 'completed',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->post('/api/payments', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['payment']);
    }

    public function testUpdateApayment()
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create();

        $paymentData = [
            'booking_id' => $booking->id,
            'amount' => 150.75,
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->put('/api/payments/'. $payment->id, $paymentData);

        $response->assertStatus(200);
        $response->assertJsonStructure(['payment']);
    }

    public function testDeleteAPayment()
    {
        $payment = Payment::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->delete('/api/payments/'. $payment->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function testCompleteCustomers()
    {
        $booking = Booking::factory()->create();

        $data = [
            'booking_id' => $booking->id,
            'amount' => 100.50,
            'payment_date' => now()->toDateString(),
            'status' => 'completed',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->post('/api/payments', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['payment']);

        $payment = $response->json()['payment'];
        $paymentId  = $payment['id'];

        $updatePaymentData = [
            'amount' => 180,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->put('/api/payments/'. $paymentId, $updatePaymentData);

        $response->assertStatus(200);
        $response->assertJsonStructure(['payment']);

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/payments/'. $paymentId);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('payments', ['id' => $paymentId]);
    }

    public static function dataThatShouldFail(): array {
        $bookingId = rand();
        $amount = 100;
        $paymentDate = '2024-01-31';
        $status = 'completed';

        return [
            'no_booking_id' => [
                [
                    'amount' => $amount,
                    'payment_date' => $paymentDate,
                    'status' => $status
                ],
            ],
            'no_amount' => [
                [
                    'bookingId' => $bookingId,
                    'payment_date' => $paymentDate,
                    'status' => $status
                ],
            ],
            'no_payment_date' => [
                [
                    'bookingId' => $bookingId,
                    'amount' => $amount,
                    'status' => $status
                ],
            ],
            'no_status' => [
                [
                    'bookingId' => $bookingId,
                    'amount' => $amount,
                    'payment_date' => $paymentDate,
                ],
            ],
            'invalid_booking_id' => [
                [
                    'bookingId' => '',
                    'amount' => $amount,
                    'payment_date' => $paymentDate,
                    'status' => $status,
                ],
            ],
            'invalid_amount' => [
                [
                    'bookingId' => $bookingId,
                    'amount' => 'amount',
                    'payment_date' => $paymentDate,
                    'status' => $status,
                ],
            ],
            'invalid_payment_date' => [
                [
                    'bookingId' => $bookingId,
                    'amount' => $amount,
                    'payment_date' => 'paymentDate',
                    'status' => $status,
                ],
            ],
            'invalid_status' => [
                [
                    'bookingId' => $bookingId,
                    'amount' => $amount,
                    'payment_date' => $paymentDate,
                    'status' => 'status',
                ],
            ],
        ];
    }

}
