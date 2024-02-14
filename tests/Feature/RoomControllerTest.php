<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoomControllerTest extends TestCase
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
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/rooms', $requestData);

        $response->assertStatus(400);
    }

    public function testUnauthenticatedUserCanNotCreateARoom()
    {
        $roomData = [
            'number' => 101,
            'type' => 'Single',
            'price_per_night' => 100.00,
            'status' => 'available',
        ];

        $response = $this->postJson('/api/rooms', $roomData);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotUpdateARoom()
    {
        $room = Room::factory()->create();

        $updatedRoomData = [
            'number' => $room->number,
            'type' => 'Double',
            'price_per_night' => 120.00,
            'status' => 'available',
        ];

        $response = $this->putJson('/api/rooms/'. $room->id, $updatedRoomData);

        $response->assertStatus(401);
    }

    public function testUnauthenticatedUserCanNotDeleteARoom()
    {
        $room = Room::factory()->create();

        $response = $this->deleteJson('/api/rooms/'. $room->id);

        $response->assertStatus(401);
    }

    public function testTryToUpdateARoomWithoutRoomId()
    {
        $room = Room::factory()->create();

        $updatedRoomData = [
            'number' => $room->number,
            'type' => 'Double',
            'price_per_night' => 120.00,
            'status' => 'available',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/rooms/', $updatedRoomData);

        $response->assertStatus(405);
    }

    public function testTryToUpdateARoomWithNoExistingRoomId()
    {
        $updatedRoomData = [
            'number' => 1,
            'type' => 'Double',
            'price_per_night' => 120.00,
            'status' => 'available',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/rooms/1000000000', $updatedRoomData);

        $response->assertStatus(404);
    }

    public function testTryToDeleteARoomWithoutRoomId()
    {
        $room = Room::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/rooms/');

        $response->assertStatus(405);
    }

    public function testTryToDeleteARoomWithNoExistingRoomId()
    {
        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/rooms/1000000000');

        $response->assertStatus(404);
    }

    public function testGetASingleRoom()
    {
        $room = Room::factory()->create();

        $response = $this->get('/api/rooms/'. $room->id);

        $response->assertStatus(200);
        $response->assertJson(['room' => [
            'id' => $room->id,
            'number' => $room->number,
            'price_per_night' => $room->price_per_night,
            'status' => $room->status,
            'type' => $room->type,
        ]]);
    }

    public function testListRooms()
    {
        Room::factory()->count(3)->create();

        $response = $this->get('/api/rooms');

        $response->assertStatus(200);

        $roomsJson = $response->json();
        $this->assertCount(3, $roomsJson['rooms']);

    }

    public function testCreateARoom()
    {
        $roomData = [
            'number' => '101',
            'type' => 'Single',
            'price_per_night' => 100.00,
            'status' => 'available',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/rooms', $roomData);

        $response->assertStatus(200);
        $response->assertJsonFragment($roomData);
    }

    public function testUpdateARoom()
    {
        $room = Room::factory()->create();

        $updatedRoomData = [
            'type' => 'Double Updated',
            'price_per_night' => 120.00,
            'status' => 'available',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/rooms/'. $room->id, $updatedRoomData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updatedRoomData);
    }

    public function testDeleteARoom()
    {
        $room = Room::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/rooms/'. $room->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function testCompleteRooms()
    {
        $roomData = [
            'number' => '1001',
            'type' => 'Single 1',
            'price_per_night' => 100.00,
            'status' => 'available',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->postJson('/api/rooms', $roomData);

        $response->assertStatus(200);
        $response->assertJsonFragment($roomData);

        $room = $response->json()['room'];
        $roomId  = $room['id'];

        $updatedRoomData = [
            'type' => 'Double Updated',
            'price_per_night' => 125.00,
            'status' => 'available',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->putJson('/api/rooms/'. $roomId, $updatedRoomData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updatedRoomData);

        $response = $this->withHeader('Authorization', 'Bearer '. $this->plainTextToken)->deleteJson('/api/rooms/'. $roomId);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('rooms', ['id' => $roomId]);
    }

    public static function dataThatShouldFail(): array
    {
        $number = '100';
        $type = 'Single';
        $pricePerNight = 100.00;
        $status = 'available';

        return [
            'no_number' => [
                [
                    'type' => $type,
                    'price_per_night' => $pricePerNight,
                    'status' => $status,
                ],
            ],
            'no_type' => [
                [
                    'number' => $number,
                    'price_per_night' => $pricePerNight,
                    'status' => $status,
                ],
            ],
            'no_price_per_night' => [
                [
                    'number' => $number,
                    'type' => $type,
                    'status' => $status,
                ],
            ],
            'no_status' => [
                [
                    'number' => $number,
                    'type' => $type,
                    'price_per_night' => $pricePerNight,
                ],
            ],
            'invalid_number' => [
                [
                    'number' => 'number',
                    'type' => $type,
                    'price_per_night' => $pricePerNight,
                    'status' => $status,
                ],
            ],
            'invalid_type' => [
                [
                    'number' => $number,
                    'type' => '',
                    'price_per_night' => $pricePerNight,
                    'status' => $status,
                ],
            ],
            'invalid_price_per_night' => [
                [
                    'number' => $number,
                    'type' => $type,
                    'price_per_night' => 'price_per_night',
                    'status' => $status,
                ],
            ],
            'invalid_status' => [
                [
                    'number' => $number,
                    'type' => $type,
                    'price_per_night' => $pricePerNight,
                    'status' => 1,
                ],
            ],
        ];
    }
}