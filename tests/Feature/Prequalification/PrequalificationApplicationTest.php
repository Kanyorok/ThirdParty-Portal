<?php

namespace Tests\Feature\Prequalification;

use App\Models\Auth\User;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrequalificationApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run only the migrations needed (fallback to full for simplicity)
        $this->artisan('migrate');
    }

    public function test_supplier_cannot_apply_twice_to_same_round(): void
    {
        // Manually create user & round since factories may not exist in this codebase
        $user = User::create([
            'UserID' => 'tester' . uniqid(),
            'Name' => 'Test Supplier',
            'Email' => uniqid('test') . '@example.com',
            'Password' => bcrypt('password'),
        ]);

        $round = PrequalificationRound::create([
            'Title' => 'Round A',
            'Description' => 'Desc',
            'StartDate' => now(),
            'EndDate' => now()->addDays(30),
            'MaxVendors' => 5,
            'Status' => \App\Enums\Procurement\PrequalificationRoundEnum::Open,
            'CreatedBy' => $user->Id,
        ]);

        // Seed first application
        PrequalificationApplication::create([
            'SupplierID' => $user->Id,
            'RoundID' => $round->RoundID,
            'Status' => \App\Enums\Procurement\PrequalificationApplicationEnum::Submitted,
            'SubmittedOn' => now(),
            'CreatedBy' => $user->Id,
        ]);

        $this->actingAs($user, 'sanctum');

        $payload = [
            'round_id' => $round->RoundID,
            'category_ids' => [],
        ];

        $response = $this->postJson(route('api.procurement.prequalification.applications.store'), $payload);

        $response->assertStatus(409)
            ->assertJsonStructure([
                'message', 'applicationId', 'roundId',
            ]);
    }

    public function test_different_users_can_each_apply_to_same_round(): void
    {
        $userA = User::create([
            'UserID' => 'userA' . uniqid(),
            'Name' => 'User A',
            'Email' => uniqid('userA') . '@example.com',
            'Password' => bcrypt('password'),
        ]);
        $userB = User::create([
            'UserID' => 'userB' . uniqid(),
            'Name' => 'User B',
            'Email' => uniqid('userB') . '@example.com',
            'Password' => bcrypt('password'),
        ]);

        $round = PrequalificationRound::create([
            'Title' => 'Round B',
            'Description' => 'Desc',
            'StartDate' => now(),
            'EndDate' => now()->addDays(30),
            'MaxVendors' => 5,
            'Status' => \App\Enums\Procurement\PrequalificationRoundEnum::Open,
            'CreatedBy' => $userA->Id,
        ]);

        // First application by User A
        $this->actingAs($userA, 'sanctum');
        $payload = ['round_id' => $round->RoundID, 'category_ids' => []];
        $this->postJson(route('api.procurement.prequalification.applications.store'), $payload)
            ->assertStatus(201);

        // Second distinct application by User B should succeed (201)
        $this->actingAs($userB, 'sanctum');
        $this->postJson(route('api.procurement.prequalification.applications.store'), $payload)
            ->assertStatus(201);
    }
}
