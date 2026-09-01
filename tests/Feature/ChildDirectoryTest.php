<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Enums\SpeechStatusEnum;
use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_children_directory()
    {
        $response = $this->getJson('/api/user/children');

        $response->assertStatus(401);
    }

    public function test_context_isolation_parent_only_sees_own_children()
    {
        $parentA = User::factory()->create();
        $parentB = User::factory()->create();

        // Create 2 children for Parent A
        Child::factory()->count(2)->create([
            'parent_id' => $parentA->id,
        ]);

        // Create 3 children for Parent B
        Child::factory()->count(3)->create([
            'parent_id' => $parentB->id,
        ]);

        // Act as Parent A
        $response = $this->actingAs($parentA, 'user')->getJson('/api/user/children');

        $response->assertStatus(200);
        
        // Assert we only get Parent A's 2 children
        $response->assertJsonCount(2, 'data');
    }

    public function test_successful_data_retrieval_and_metric_formatting()
    {
        $parent = User::factory()->create();

        $child = Child::factory()->create([
            'parent_id' => $parent->id,
            'name' => 'John Doe',
            'age' => 5,
            'gender' => GenderEnum::MALE->value,
            'autism_level' => AutismLevelEnum::MILD->value,
            'speech_status' => SpeechStatusEnum::VERBAL->value,
        ]);

        $response = $this->actingAs($parent, 'user')->getJson('/api/user/children');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'age',
                    'gender',
                    'gender_label',
                    'autism_level',
                    'autism_level_label',
                    'speech_status',
                    'speech_status_label',
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'name' => 'John Doe',
            'age' => 5,
            'autism_level' => AutismLevelEnum::MILD->value,
            'speech_status' => SpeechStatusEnum::VERBAL->value,
        ]);
    }

    public function test_empty_state_for_parent_with_no_children()
    {
        $parent = User::factory()->create();

        $response = $this->actingAs($parent, 'user')->getJson('/api/user/children');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => []
        ]);
    }
}
