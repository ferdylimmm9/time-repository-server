<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Admin\JurusanLiteResource;
use App\Http\Resources\Admin\TesisLiteResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
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
            'jurusan'         => new JurusanLiteResource($this->jurusan),
            'tesis'           => TesisLiteResource::collection($this->tesis),
        ];
    }
}
