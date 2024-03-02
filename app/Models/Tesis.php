<?php

namespace App\Models;

use App\Models\Abstract\BaseModel;
use App\Models\Enum\StatusTesis;
use App\Models\Enum\TipeTesis;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tesis extends BaseModel
{
    protected $table = 'tesis';

    protected $fillable = [
        'status',
        'jurusan_id',
        'fakultas_id',
        'judul',
        'tipe',
        'is_aktif',
        'abstrak',
        'waktu_disetujui',
    ];

    protected $casts = [
        'waktu_dibuat'    => 'datetime',
        'waktu_diubah'    => 'datetime',
        'waktu_disetujui' => 'datetime',
    ];

    protected $attributes = [
        'status' => StatusTesis::PENDING,
    ];

    public function getRules(): array
    {
        return [
            'jurusan_id'      => 'required|exists:jurusan,id',
            'fakultas_id'     => 'required|exists:fakultas,id',
            'judul'           => 'required|string',
            'tipe'            => ['required', new EnumValue(TipeTesis::class)],
            'status'          => ['required', new EnumValue(StatusTesis::class)],
            'abstrak'         => 'required|string',
            'waktu_disetujui' => 'nullable|date',
        ];
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id');
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tesis');
    }

    public function file(): HasMany
    {
        return $this->hasMany(File::class, 'tesis_id');
    }
}
