<?php

namespace App\Models;

use App\Models\Abstract\BaseModel;
use App\Models\Enum\KodeJurusan;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends BaseModel
{
    protected $table = 'jurusan';

    protected $fillable = [
        'fakultas_id',
        'nama',
        'kode',
    ];

    protected $casts = [
        'waktu_dibuat' => 'datetime',
        'waktu_diubah' => 'datetime',
    ];

    public function getRules(): array
    {
        return [
            'fakultas_id'  => 'required|uuid|exists:fakultas,id',
            'nama'         => 'required|string',
            'kode'         => 'required|string|unique:jurusan,kode,' . $this->id,
        ];
    }

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tesis(): HasMany
    {
        return $this->hasMany(Tesis::class);
    }
}
