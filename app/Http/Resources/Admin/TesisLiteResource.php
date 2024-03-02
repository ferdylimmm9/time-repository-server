<?php

namespace App\Http\Resources\Admin;

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
            'status'       => $this->status,
            'judul'        => $this->judul,
            'type'         => $this->tipe,
            'waktu_dibuat' => $this->waktu_dibuat,
            'user'         => UserLiteResource::collection($this->user),
        ];
    }
}
