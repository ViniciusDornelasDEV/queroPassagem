<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $company = data_get($this->resource, 'company', []);

        return [
            'id' => data_get($this->resource, 'id'),
            'company' => [
                'id' => data_get($company, 'id'),
                'name' => data_get($company, 'name'),
                'logo' => data_get($company, 'logo'),
            ],
            'from' => [
                'id' => data_get($this->resource, 'from.id'),
                'name' => data_get($this->resource, 'from.name'),
            ],
            'to' => [
                'id' => data_get($this->resource, 'to.id'),
                'name' => data_get($this->resource, 'to.name'),
            ],
            'departure' => [
                'date' => data_get($this->resource, 'departure.date'),
                'time' => data_get($this->resource, 'departure.time'),
            ],
            'arrival' => [
                'date' => data_get($this->resource, 'arrival.date'),
                'time' => data_get($this->resource, 'arrival.time'),
            ],
            'price' => [
                'seat' => data_get($this->resource, 'price.seatPrice'),
                'tax' => data_get($this->resource, 'price.taxPrice'),
                'total' => data_get($this->resource, 'price.price'),
                'insurance' => data_get($this->resource, 'price.insurance'),
            ],
            'availableSeats' => data_get($this->resource, 'availableSeats'),
            'seatClass' => data_get($this->resource, 'seatClass'),
            'travelDuration' => $this->formatDuration(data_get($this->resource, 'travelDuration')),
        ];
    }

    private function formatDuration(?int $seconds): ?string
{
    if (! $seconds || $seconds <= 0) {
        return null;
    }
        $minutes = intdiv($seconds, 60);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}min";
    }
}
