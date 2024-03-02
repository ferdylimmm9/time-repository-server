<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TesisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'tipe'    => $this->tipe,
            'status'  => $this->status,
            'judul'   => $this->judul,
            'abstrak' => $this->abstrak,
            'jurusan' => new JurusanLiteResource($this->jurusan),
            'user'    => UserLiteResource::collection($this->user),
            'files'   => FileResource::collection($this->file),
        ];
    }
}
