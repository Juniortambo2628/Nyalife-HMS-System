<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'staff_id' => $this->staff_id,
            'user_id' => $this->user_id,
            'specialization' => $this->specialization,
            'department' => $this->department,
            'license_number' => $this->license_number,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
        ];
    }
}
