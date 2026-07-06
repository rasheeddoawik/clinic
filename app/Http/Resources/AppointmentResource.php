<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            '$id' => (string)$this->id,
            'userId' => (string)$this->user_id,
            'patient' => [
                '$id' => (string)$this->patient_id,
                'name' => $this->user->name ?? 'Unknown Patient',
            ],
            'status' => $this->status,
            'schedule' => $this->schedule ? $this->schedule->toIso8601String() : now()->toIso8601String(),
            'primaryPhysician' => $this->primary_physician,
            'reason' => $this->reason,
            'note' => $this->note,
            'cancellationReason' => $this->cancellation_reason,
        ];
    }
}