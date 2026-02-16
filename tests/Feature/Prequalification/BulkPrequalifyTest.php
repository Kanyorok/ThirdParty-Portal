<?php

namespace Tests\Feature\Prequalification;

use App\Models\Auth\User;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationResult;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\ThirdParies\Supplier;
use App\Models\ThirdParty\ThirdParties;
use Tests\TestCase;

class BulkPrequalifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_bulk_prequalify_marks_passed_suppliers_active(): void
    {
        // Create an authenticated evaluator
        $user = User::create([
            'UserID' => 'evaluator' . uniqid(),
            'Name' => 'Evaluator',
            'Email' => uniqid('eval') . '@example.com',
            'Phone' => '0700000000',
            'Password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        // Create a round
        $round = PrequalificationRound::create([
            'Title' => 'Round Bulk',
            'Description' => 'Desc',
            'StartDate' => now(),
            'EndDate' => now()->addDays(10),
            'MaxVendors' => 10,
            'Status' => \App\Enums\Procurement\PrequalificationRoundEnum::Open,
            'CreatedBy' => $user->Id,
        ]);

        // Create two suppliers (ThirdParties) and applications
        $tpPassed = ThirdParties::create([
            'ThirdPartyName' => 'ACME Passed',
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);
        $tpFailed = ThirdParties::create([
            'ThirdPartyName' => 'ACME Failed',
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        $appPassed = PrequalificationApplication::create([
            'SupplierID' => $tpPassed->Id,
            'RoundID' => $round->RoundID,
            'Status' => \App\Enums\Procurement\PrequalificationApplicationEnum::Submitted,
            'SubmittedOn' => now(),
            'CreatedBy' => $user->Id,
        ]);
        $appFailed = PrequalificationApplication::create([
            'SupplierID' => $tpFailed->Id,
            'RoundID' => $round->RoundID,
            'Status' => \App\Enums\Procurement\PrequalificationApplicationEnum::Submitted,
            'SubmittedOn' => now(),
            'CreatedBy' => $user->Id,
        ]);

        // Create results: one Passed, one Failed
        PrequalificationResult::create([
            'ApplicationID' => $appPassed->ApplicationID,
            'TotalScore' => 85.25,
            'Decision' => 'Passed',
        ]);
        PrequalificationResult::create([
            'ApplicationID' => $appFailed->ApplicationID,
            'TotalScore' => 52.40,
            'Decision' => 'Failed',
        ]);

        // Call bulk prequalify
        $response = $this->postJson(route('prequalification.prequalification-evaluation.prequalify.bulk', $round->RoundID));
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Assert ThirdParty for passed is marked prequalified
        $this->assertDatabaseHas('t_ThirdParties', [
            'Id' => $tpPassed->Id,
            'IsPrequalified' => 1,
        ]);
        // Assert supplier row created/updated active for passed
        $this->assertDatabaseHas('t_Suppliers', [
            'ThirdPartyID' => $tpPassed->Id,
            'RoundID' => $round->RoundID,
            'Active_Status' => 1,
        ]);
        // Assert failed remains not prequalified and supplier not activated
        $this->assertDatabaseHas('t_ThirdParties', [
            'Id' => $tpFailed->Id,
            'IsPrequalified' => 0,
        ]);
        $this->assertDatabaseMissing('t_Suppliers', [
            'ThirdPartyID' => $tpFailed->Id,
            'RoundID' => $round->RoundID,
            'Active_Status' => 1,
        ]);
    }
}
