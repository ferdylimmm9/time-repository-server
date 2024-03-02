<?php

namespace App\Models;

use App\Models\Abstract\BaseModel;
use App\Models\Enum\TipeFile;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends BaseModel
{
    protected $table = 'file';

    protected $fillable = [
        'tesis_id',
        'nama',
        'tipe',
    ];

    protected $casts = [
        'waktu_dibuat' => 'datetime',
        'waktu_diubah' => 'datetime',
    ];

    public function getRules(): array
    {
        return [
            'tesis_id'     => 'required|uuid|exists:tesis,id',
            'nama'         => 'required|string',
            'tipe'         => ['required', new EnumValue(TipeFile::class)],
        ];
    }

    public function tesis(): BelongsTo
    {
        return $this->belongsTo(Tesis::class, 'tesis_id');
    }
}
