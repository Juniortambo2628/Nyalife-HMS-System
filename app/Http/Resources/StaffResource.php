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
            'department_id' => $this->department_id,
            'department_name' => $this->departmentRelation?->department_name ?? $this->department,
            'license_number' => $this->license_number,
            'position' => $this->position,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'department_relation' => $this->whenLoaded('departmentRelation', fn () => DepartmentResource::make($this->departmentRelation)),
        ];
    }
}
