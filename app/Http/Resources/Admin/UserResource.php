<?php

namespace App\Http\Resources\Admin;

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
            'id'              => $this->id,
            'nomor_identitas' => $this->nomor_identitas,
            'nama_depan'      => $this->nama_depan,
            'nama_tengah'     => $this->nama_tengah,
            'nama_belakang'   => $this->nama_belakang,
            'tipe_user'       => $this->tipe_user,
            'jurusan'         => new JurusanLiteResource($this->jurusan),
        ];
    }
}
