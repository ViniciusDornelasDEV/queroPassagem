<?php

namespace App\Services;

use App\Integrations\QueroPassagemClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class StopService
{
    public function __construct(
        private readonly QueroPassagemClient $client,
    ) {}

    public function getStops(): array
    {
        return Cache::remember('stops', now()->addDay(), function (): array {
            return $this->normalizeStops($this->client->getStops());
        });
    }

    public function validateStop(string $id): array
    {
        foreach ($this->getStops() as $stop) {
            if ((string) ($stop['id'] ?? '') === $id) {
                return $stop;
            }
            if (! empty($stop['substops']) && is_array($stop['substops'])) {
                foreach ($stop['substops'] as $sub) {
                    if ((string) ($sub['id'] ?? '') === $id) {
                        return $sub;
                    }
                }
            }
        }

        throw ValidationException::withMessages([
            'stop' => 'Rodoviária não encontrada.',
        ]);

    }

    public function expandStopIds(string $id): array
    {
        $stop = collect($this->getStops())
            ->first(fn (array $item): bool => (string) ($item['id'] ?? '') === $id);

        if (! is_array($stop)) {
            return [$id];
        }

        if (($stop['type'] ?? '') !== 'city') {
            return [$id];
        }

        $substops = $stop['substops'] ?? [];
        if (! is_array($substops) || $substops === []) {
            return [$id];
        }

        $ids = [];

        foreach ($substops as $substop) {
            if (! is_array($substop)) {
                continue;
            }

            $substopId = (string) ($substop['id'] ?? '');
            if ($substopId === '') {
                continue;
            }

            $ids[] = $substopId;
        }

        return $ids !== [] ? array_values(array_unique($ids)) : [$id];
    }

    private function normalizeStops(array $payload): array
    {
        $allowedStates = config('queropassagem.allowed_states', []);

        $stops = $payload;

        if (isset($payload['stops']) && is_array($payload['stops'])) {
            $stops = $payload['stops'];
        }

        $normalized = [];

        foreach ($stops as $item) {
            if (! is_array($item)) {
                continue;
            }
            $normalized[] = $this->normalizeStop($item, $allowedStates);
        }

        return $normalized;
    }

    private function normalizeStop(array $item, array $allowedStates): array
    {
        $rawSubstops = $item['substops'] ?? [];
        if (! is_array($rawSubstops)) {
            $rawSubstops = [];
        }

        $substops = [];

        foreach ($rawSubstops as $sub) {
            if (! is_array($sub)) {
                continue;
            }
            $substops[] = $this->normalizeSubstop($sub, $allowedStates);
        }

        $name = (string) ($item['name'] ?? '');
        $state = $this->extractState($name);

        return [
            'id' => (string) ($item['id'] ?? ''),
            'name' => $name,
            'url' => (string) ($item['url'] ?? ''),
            'type' => (string) ($item['type'] ?? ''),
            'state' => $state,
            'allowed' => $state !== null && in_array($state, $allowedStates, true),
            'substops' => $substops,
        ];
    }

    private function normalizeSubstop(array $sub, array $allowedStates): array
    {
        $name = (string) ($sub['name'] ?? '');
        $state = $this->extractState($name);

        return [
            'id' => (string) ($sub['id'] ?? ''),
            'name' => $name,
            'url' => (string) ($sub['url'] ?? ''),
            'type' => (string) ($sub['type'] ?? 'station'),
            'state' => $state,
            'allowed' => $state !== null && in_array($state, $allowedStates, true),
        ];
    }

    private function extractState(string $name): ?string
    {
        if (preg_match('/,\s([A-Z]{2})\b/', $name, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
