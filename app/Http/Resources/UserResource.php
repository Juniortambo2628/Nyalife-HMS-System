<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array (safe for frontend; excludes password).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role_id' => $this->role_id,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'address' => $this->address,
            'profile_image' => $this->profile_image,
            'last_login' => $this->last_login instanceof \DateTimeInterface ? $this->last_login->format(\DateTimeInterface::ATOM) : $this->last_login,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            'role' => $this->role,
            'role_relation' => $this->whenLoaded('roleRelation'),
        ];
    }
}
