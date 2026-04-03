<?php

namespace App\Integrations;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use RuntimeException;
use Throwable;

class QueroPassagemClient
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function getStops(): array
    {
        return $this->request()->get('/stops')->throw()->json();
    }

    private function request(): PendingRequest
    {
        $baseUrl = (string) config('services.queropassagem.base_url');
        $user = (string) config('services.queropassagem.user');
        $password = (string) config('services.queropassagem.password');
        $affiliate = config('services.queropassagem.affiliate');

        if ($baseUrl === '' || $user === '' || $password === '') {
            throw new RuntimeException('QueroPassagem credentials are not configured.');
        }

        try {
            return $this->http
                ->baseUrl(rtrim($baseUrl, '/'))
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->withBasicAuth($user, $password)
                ->when(
                    filled($affiliate),
                    fn (PendingRequest $request): PendingRequest => $request->withHeader('X-Affiliate', (string) $affiliate),
                );
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to initialize QueroPassagem HTTP client.', 0, $exception);
        }
    }
}
