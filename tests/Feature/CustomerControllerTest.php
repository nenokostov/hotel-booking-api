<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
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
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/customers', $requestData);

        $response->assertStatus(400);
    }

    public function testUnauthenticatedUserCanNotCreateACustomer()
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '1234567890',
        ]);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotUpdateACustomer()
    {
        $customer = Customer::factory()->create();

        $response = $this->putJson('/api/customers/'. $customer->id, [
            'name' => 'John Doe Updated',
            'email' => 'johnUpdated@example.com',
            'phone_number' => '1234567890',
        ]);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotDeleteAcustomer()
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson('/api/customers/'. $customer->id);

        $response->assertStatus(401);
    }

    public function testTryToUpdateAcustomerWithoutCustomerId()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/customers/', [
            'name' => 'John Doe Updated',
            'email' => 'johnUpdated@example.com',
            'phone_number' => '1234567890',
        ]);

        $response->assertStatus(405);
    }

    public function testTryToUpdateACustomerWithNoExistingCustomerId()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/customers/1000000000', [
            'name' => 'John Doe Updated',
            'email' => 'johnUpdated@example.com',
            'phone_number' => '1234567890',
        ]);

        $response->assertStatus(404);
    }

    public function testTryToDeleteACustomerWithoutCustomerId() {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/customers/');

        $response->assertStatus(405);
    }

    public function testTryToDeleteACustomerWithNoExistingCustomerId() {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/customers/1000000000');

        $response->assertStatus(404);
    }

    public function testGetASingleCustomer()
    {
        $customer = Customer::factory()->create();

        $response = $this->get('/api/customers/'. $customer->id);

        $response->assertStatus(200);
        $response->assertJson(['customer' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone_number' => $customer->phone_number,
        ]]);
    }

    public function testListCustomers()
    {
        $customer = Customer::factory(3)->create();

        $response = $this->get('/api/customers');

        $response->assertStatus(200);

        $customersJson = $response->json();
        $this->assertCount(3, $customersJson['customers']);
    }

    public function testCreateACustomer()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/customers', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '1234567890',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '1234567890',
        ]);
    }

    public function testUpdateACustomer()
    {
        $customer = Customer::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/customers/'. $customer->id, [
            'name' => 'John Doe Updated',
            'email' => 'johnUpdated@example.com',
            'phone_number' => '1234567890',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'John Doe Updated',
        ]);
    }

    public function testDeleteACustomer()
    {
        $customer = Customer::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/customers/'. $customer->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function testCompleteCustomers()
    {
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '1234567890',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/customers', $customerData);

        $response->assertStatus(200);
        $response->assertJsonFragment($customerData);

        $customer = $response->json()['customer'];
        $customerId  = $customer['id'];

        $updatedCustomerData = [
            'name' => 'John Doe Updated',
            'email' => 'johnUpdated@example.com',
            'phone_number' => '1234567890',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/customers/'. $customerId, $updatedCustomerData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updatedCustomerData);

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/customers/'. $customerId);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('customers', ['id' => $customerId]);
    }

    public static function dataThatShouldFail(): array {
        $name = 'John Doe';
        $email = 'john@example.com';
        $phoneNumber = '1234567890';

        return [
            'no_name' => [
                [
                    'email' => $email,
                    'phone_number' => $phoneNumber,
                ],
            ],
            'no_email' => [
                [
                    'name' => $name,
                    'phone_number' => $phoneNumber,
                ],
            ],
            'no_phone_number' => [
                [
                    'name' => $name,
                    'email' => $email,
                ],
            ],
            'invalid_name' => [
                [
                    'name' => '',
                    'email' => $email,
                    'phone_number' => $phoneNumber,
                ],
            ],
            'invalid_email' => [
                [
                    'name' => $name,
                    'email' => '',
                    'phone_number' => $phoneNumber,
                ],
            ],
            'invalid_phone_number' => [
                [
                    'name' => $name,
                    'email' => $email,
                    'phone_number' => '',
                ],
            ],
        ];
    }
}
