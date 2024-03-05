<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Admin\UserLiteResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TesisLiteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'judul'        => $this->judul,
            'waktu_dibuat' => $this->waktu_dibuat,
            'user'         => UserLiteResource::collection($this->user),
        ];
    }
}
