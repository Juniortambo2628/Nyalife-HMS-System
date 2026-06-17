<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'department_id' => $this->department_id,
            'department_name' => $this->department_name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'code' => $this->code,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'head_name' => $this->head_name,
            'head_position' => $this->head_position,
            'head_image' => $this->head_image,
            'staff_count' => $this->when(isset($this->staff_members_count), $this->staff_members_count),
            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format(\DateTimeInterface::ATOM)
                : $this->created_at,
            'staff_members' => $this->whenLoaded('staffMembers', fn () => StaffResource::collection(
                $this->staffMembers->loadMissing('user')
            )),
        ];
    }
}
