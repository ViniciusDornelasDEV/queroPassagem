<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeatApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.queropassagem.base_url' => 'https://queropassagem.test',
            'services.queropassagem.user' => 'test-user',
            'services.queropassagem.password' => 'test-pass',
            'services.queropassagem.affiliate' => null,
            'services.api.key' => 'test-api-key',
        ]);
    }

    private function apiHeaders(): array
    {
        return ['X-API-KEY' => 'test-api-key'];
    }

    public function test_post_seats_returns_successful_standardized_payload(): void
    {
        Http::fake([
            'https://queropassagem.test/new/seats' => Http::response([
                [
                    'seats' => [
                        [
                            [
                                'type' => 'seat',
                                'seat' => '12',
                                'occupied' => false,
                                'position' => ['x' => 2, 'y' => 4],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/seats', [
            'travelId' => '123',
            'orientation' => 'horizontal',
            'type' => 'matrix',
        ], $this->apiHeaders());

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                [
                    'seat_number' => '12',
                    'occupied' => false,
                    'type' => 'seat',
                    'position' => [
                        'x' => 2,
                        'y' => 4,
                    ],
                ],
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://queropassagem.test/new/seats'
                && $request->method() === 'POST'
                && $request->data() === [
                    'travelId' => '123',
                    'orientation' => 'horizontal',
                    'type' => 'matrix',
                ];
        });
    }

    public function test_post_seats_validation_error_returns_unsuccessful_response(): void
    {
        Http::fake();

        $response = $this->postJson('/api/v1/seats', [], $this->apiHeaders());

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        Http::assertNothingSent();
    }

    public function test_post_seats_without_api_key_returns_unauthorized(): void
    {
        Http::fake();

        $response = $this->postJson('/api/v1/seats', [
            'travelId' => '123',
            'orientation' => 'horizontal',
            'type' => 'matrix',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'error' => [
                'type' => 'authorization_error',
                'message' => 'Invalid API Key',
            ],
        ]);

        Http::assertNothingSent();
    }
}
