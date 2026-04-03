<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;

class PendingInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_ok_for_authenticated_user()
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/pending-invoices/summary', $payload);

        $response->assertOk();
        $this->assertIsArray($response->json());
    }

    public function test_issue_accepts_valid_payload_and_returns_no_content()
    {
        Sanctum::actingAs(User::factory()->create());

        $clients = Client::factory()->count(2)->create();

        $payload = [
            'date' => now()->toDateString(),
            'client_ids' => $clients->pluck('id')->all(),
        ];

        $response = $this->postJson('/api/pending-invoices/issue', $payload);

        $response->assertNoContent();
    }

    public function test_request_pdf_stores_parameters_in_cache_and_returns_uuid()
    {
        Sanctum::actingAs(User::factory()->create());

        $client = Client::factory()->create();

        $payload = [
            'date' => now()->toDateString(),
            'client_ids' => [$client->id],
        ];

        $response = $this->postJson('/api/pending-invoices/request-pdf', $payload);

        $response->assertOk();
        $response->assertJsonStructure(['uuid']);

        $uuid = $response->json('uuid');

        $this->assertNotNull($uuid);
        $this->assertSame($payload, Cache::get($uuid));
    }
}

