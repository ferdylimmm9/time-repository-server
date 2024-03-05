<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Admin\FileResource;
use App\Http\Resources\Admin\UserLiteResource;
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
            'id'           => $this->id,
            'status'       => $this->status,
            'abstrak'      => $this->abstrak,
            'tipe'         => $this->tipe,
            'fakultas'     => $this->fakultas->nama,
            'jurusan'      => $this->jurusan->nama,
            'waktu_dibuat' => $this->waktu_dibuat,
            'user'         => UserLiteResource::collection($this->user),
//            'file'         => FileResource::collection($this->file),
        ];
    }
}
