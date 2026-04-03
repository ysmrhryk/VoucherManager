<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\PaymentMethod;

class PaymentMethodControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_list_for_authenticated_user(): void
    {
        Sanctum::actingAs(User::factory()->create());

        PaymentMethod::factory()->count(2)->create();

        $response = $this->getJson('/api/payment-methods');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    public function test_store_creates_payment_method(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $data = ['value' => '銀行振込'];

        $response = $this->postJson('/api/payment-methods', $data);

        $response->assertCreated()
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('payment_methods', $data);
    }

    public function test_show_returns_single_payment_method(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $paymentMethod = PaymentMethod::factory()->create(['value' => '表示テスト']);

        $response = $this->getJson("/api/payment-methods/{$paymentMethod->id}");

        $response->assertOk()
            ->assertJsonFragment(['value' => '表示テスト']);
    }

    public function test_destroy_deletes_payment_method(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->deleteJson("/api/payment-methods/{$paymentMethod->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('payment_methods', ['id' => $paymentMethod->id]);
    }
}

