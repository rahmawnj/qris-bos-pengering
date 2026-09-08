<?php

namespace Tests\Feature;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OwnerWithdrawalFeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_store_withdrawal_fee_setting(): void
    {
        $this->assertTrue(Schema::hasColumn('owners', 'withdrawal_fee_charged'));

        $user = User::factory()->create([
            'role' => 'owner',
        ]);

        $owner = Owner::create([
            'user_id' => $user->id,
            'brand_name' => 'Test Brand',
            'brand_email' => 'brand@example.com',
            'address' => 'Test address',
            'balance' => 0,
            'withdrawal_fee_charged' => true,
        ]);

        $this->assertTrue((bool) $owner->fresh()->withdrawal_fee_charged);
    }
}
