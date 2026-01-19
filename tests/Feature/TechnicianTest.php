<?php

namespace Tests\Feature;

use App\Models\Technician;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TechnicianTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_can_create_technician(): void
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'dni' => $this->faker->unique()->numerify('########'),
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'availability_date' => now()->addDays(5)->toDateTimeString(),
        ];

        $response = $this->postJson('/api/technicians', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['dni' => $data['dni']]);

        $this->assertDatabaseHas('technicians', ['dni' => $data['dni']]);
    }

    public function test_can_list_technicians(): void
    {
        // Ensure at least one technician exists
        $user = User::factory()->create();
        Technician::create([
            'user_id' => $user->id,
            'dni' => '12345678',
        ]);

        $response = $this->getJson('/api/technicians');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'user_id', 'dni', 'created_at', 'updated_at']
            ]);
    }

    public function test_can_show_technician(): void
    {
        $user = User::factory()->create();
        $technician = Technician::create([
            'user_id' => $user->id,
            'dni' => $this->faker->unique()->numerify('########'),
        ]);

        $response = $this->getJson("/api/technicians/{$technician->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $technician->id]);
    }

    public function test_can_update_technician(): void
    {
        $user = User::factory()->create();
        $technician = Technician::create([
            'user_id' => $user->id,
            'dni' => $this->faker->unique()->numerify('########'),
            'city' => 'Old City'
        ]);

        $updateData = ['city' => 'New City'];

        $response = $this->putJson("/api/technicians/{$technician->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['city' => 'New City']);

        $this->assertDatabaseHas('technicians', [
            'id' => $technician->id,
            'city' => 'New City'
        ]);
    }

    public function test_can_delete_technician(): void
    {
        $user = User::factory()->create();
        $technician = Technician::create([
            'user_id' => $user->id,
            'dni' => $this->faker->unique()->numerify('########'),
        ]);

        $response = $this->deleteJson("/api/technicians/{$technician->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('technicians', ['id' => $technician->id]);
    }
}
