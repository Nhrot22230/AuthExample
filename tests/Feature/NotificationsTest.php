<?php

namespace Tests\Feature;

use App\Models\Notifications;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Usuario::factory()->create([
            'password' => Hash::make('password123')
        ]);
        $this->actingAs($this->user);
    }

    public function test_it_creates_notifications_for_specified_users()
    {
        $user2 = Usuario::factory()->create();
        $user3 = Usuario::factory()->create();

        $data = [
            'title' => 'Important Update',
            'message' => 'This is a test notification',
            'message_type' => 'info',
            'usuarios' => [$user2->id, $user3->id]
        ];

        $response = $this->postJson('/api/v1/notifications/notify', $data);
        
        $response->assertStatus(200);
        $response->assertJsonCount(2);

        $this->assertDatabaseHas('notifications', [
            'title' => 'Important Update',
            'message' => 'This is a test notification',
            'message_type' => 'info',
            'usuario_id' => $user2->id,
            'status' => false
        ]);
        $this->assertDatabaseHas('notifications', [
            'title' => 'Important Update',
            'message' => 'This is a test notification',
            'message_type' => 'info',
            'usuario_id' => $user3->id,
            'status' => false
        ]);
    }

    public function test_it_requires_all_fields_for_notifying_users()
    {
        $data = [
            'title' => '',
            'message' => '',
            'message_type' => '',
            'usuarios' => ''
        ];

        $response = $this->postJson('/api/v1/notifications/notify', $data);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'message', 'message_type', 'usuarios']);
    }

    public function test_user_can_fetch_their_notifications()
    {
        $notification = Notifications::factory()->create([
            'usuario_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/notifications/myNotifications');
        
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $notification->id,
            'title' => $notification->title,
        ]);
    }

    public function test_it_updates_notification_status()
    {
        $notification = Notifications::factory()->create([
            'usuario_id' => $this->user->id,
            'status' => false,
        ]);

        $data = ['status' => true];
        
        $response = $this->putJson("/api/v1/notifications/{$notification->id}", $data);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => true,
        ]);
    }

    public function test_it_requires_status_to_update_notification()
    {
        $notification = Notifications::factory()->create([
            'usuario_id' => $this->user->id,
            'status' => false,
        ]);

        $response = $this->putJson("/api/v1/notifications/{$notification->id}", []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_it_denies_updating_other_users_notifications()
    {
        $otherUser = Usuario::factory()->create();
        $notification = Notifications::factory()->create([
            'usuario_id' => $otherUser->id,
            'status' => false,
        ]);

        $data = ['status' => true];
        
        $response = $this->putJson("/api/v1/notifications/{$notification->id}", $data);
        
        $response->assertStatus(404);
    }

    public function test_it_deletes_notification()
    {
        $notification = Notifications::factory()->create([
            'usuario_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_it_denies_deleting_other_users_notifications()
    {
        $otherUser = Usuario::factory()->create();
        $notification = Notifications::factory()->create([
            'usuario_id' => $otherUser->id,
        ]);

        $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");
        
        $response->assertStatus(404);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }
}
