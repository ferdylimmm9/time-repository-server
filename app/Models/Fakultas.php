<?php

namespace App\Models;

use App\Models\Abstract\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fakultas extends BaseModel
{
    protected $table = 'fakultas';

    protected $fillable = [
        'nama',
    ];

    protected $casts = [
        'waktu_dibuat' => 'datetime',
        'waktu_diubah' => 'datetime',
    ];

    public function getRules(): array
    {
        return [
            'nama'         => 'required|string',
        ];
    }

    public function jurusans(): HasMany
    {
        return $this->hasMany(Jurusan::class);
    }
}
