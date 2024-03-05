<?php

namespace App\Http\Resources\User;

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
            'nomor_identitas' => $this->nomor_identitas,
            'nama_depan' => $this->nama_depan,
            'nama_tengah' => $this->nama_tengah,
            'nama_belakang' => $this->nama_belakang,
            'jurusan' => $this->jurusan->nama,
            'fakultas' => $this->jurusan->fakultas->nama,
            'tesis' => TesisLiteResource::collection($this->tesis),
        ];
    }
}
