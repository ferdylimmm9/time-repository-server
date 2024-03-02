<?php

namespace App\Models;

use App\Models\Abstract\BaseModel;
use App\Models\Abstract\BaseUser;
use App\Models\Enum\TipeUser;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Validation\Rule;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class User extends BaseUser
{
    use HasApiTokens;

    protected $table = 'user';
    protected $fillable = [
        'nomor_identitas',
        'nama_depan',
        'nama_tengah',
        'nama_belakang',
        'tipe_user',
        'jurusan_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'waktu_dibuat' => 'datetime',
        'waktu_diubah' => 'datetime',
    ];

    public function getRules(): array
    {
        return [
            'jurusan_id'      => 'required|uuid|exists:jurusan,id',
            'nomor_identitas' => ['required', 'string', Rule::unique('user', 'nomor_identitas')->ignoreModel($this)],
            'nama_depan'      => 'required|string',
            'nama_tengah'     => 'nullable|string',
            'nama_belakang'   => 'nullable|string',
            'tipe_user'       => ['required', new EnumValue(TipeUser::class)],
        ];
    }

    public function findForPassport(string $username): User
    {
        return $this->where('id', $username)->first();
    }

    public function revoke(): void
    {
        try {
            $tokenRepository = new TokenRepository();
            $refreshTokenRepository = new RefreshTokenRepository();
            foreach ($this->tokens()->get()->where('revoked', false) ?? [] as $token) {
                $tokenRepository->revokeAccessToken($token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
            }
        } catch (Throwable $e) {
            throw new BadRequestHttpException(__('error.revoke_failed'));
        }
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function tesis(): BelongsToMany
    {
        return $this->belongsToMany(Tesis::class, 'user_tesis', 'user_id', 'tesis_id');
    }
}
