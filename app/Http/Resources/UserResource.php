<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => isset($this->avatar) ? $this->avatar : asset('images/avatar.png'),
            'otp_sent' => isset($this->otp),
            'otp_expires_at' => $this->otp_expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'token' => $this->when(isset($this->token), $this->token),
        ];
    }
}
