<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ThrottlesAttempts;
use App\Http\Resources\User\MeResource;
use App\Models\Enum\TipeUser;
use App\Models\User;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use JsonException;
use Laravel\Passport\Exceptions\OAuthServerException;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class AuthController extends AccessTokenController
{
    use ThrottlesAttempts;

    protected function throttleKeyPrefix(): string
    {
        return 'user_login';
    }

    public function login(Request $request): JsonResponse
    {
        $this->validateAttempts($request);

        $credentials = $request->validate([
            'nomor_identitas' => 'required|string',
            'password'        => ['required', 'string', Password::min(6)],
        ]);

        $user = User::where('nomor_identitas', $credentials['nomor_identitas'])->first();

        if (is_null($user) || !Hash::check($credentials['password'], $user->password)) {
            throw new UnauthorizedHttpException('xBasic', __('error.incorrect_credentials'));
        }

        $request_helper = $this->createServerRequest($request);
        $body = $request_helper->getParsedBody();
        $body['username'] = $user->id;
        $body['client_id'] = config('passport.clients.users.id');
        $body['client_secret'] = config('passport.clients.users.secret');
        $body['grant_type'] = 'password';
        $body['scope'] = '';
        try {
            $result = json_decode($this->issueToken($request_helper->withParsedBody($body))->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable|OAuthServerException|JsonException  $e) {
            throw $e;
        }

        $this->clearAttempts($request);
        return response()->json([
            'data'    => $result,
            'message' => __('success.login_success'),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $input = $request->validate([
            'nomor_identitas' => ['required', 'string', Rule::unique('user', 'nomor_identitas')],
            'nama_depan'      => 'required|string',
            'nama_tengah'     => 'nullable|string',
            'nama_belakang'   => 'nullable|string',
            'jurusan_id'      => 'required|exists:jurusan,id',
            'password'        => 'required|string|confirmed|min:6',
            'tipe_user'       => ['required', new EnumValue(TipeUser::class)],
        ]);

        $user = new User($input);
        $user->password = Hash::make($input['password']);
        $user->save();

        return response()->json([
            'message' => __('success.user_created')
        ]);
    }

    public function me(): MeResource
    {
        $user = auth()->user()->load('tesis.user');

        return new MeResource($user);
    }

    public function updateMe(Request $request): MeResource
    {
        $user = auth()->user();
        $input = $request->validate([
            'nomor_identitas' => ['required', 'string', Rule::unique('user', 'nomor_identitas')->ignoreModel($user)],
            'nama_depan'      => 'required|string',
            'nama_tengah'     => 'nullable|string',
            'nama_belakang'   => 'nullable|string',
            'jurusan_id'      => 'required|exists:jurusan,id',
        ]);

        $user->fill($input);
        $user->save();

        return (new MeResource($user))
            ->additional([
                'message' => __('success.update_me')
            ]);
    }

    public function refresh(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();
        $body['client_id'] = config('passport.clients.users.id');
        $body['client_secret'] = config('passport.clients.users.secret');
        $body['grant_type'] = 'refresh_token';
        $body['scope'] = '';

        $key = 'refresh_token:' . $body['refresh_token'];

        $result = null;

        if (Redis::get($key) !== null) {
            $result = json_decode(Redis::get('refresh_token:' . $body['refresh_token']), true, 512, JSON_THROW_ON_ERROR);
        } else {
            Cache::lock($key, 2)->block(3, function () use ($key, $body, $request, &$result) {
                if (Redis::get($key) !== null) {
                    $result = json_decode(Redis::get('refresh_token:' . $body['refresh_token']), true, 512, JSON_THROW_ON_ERROR);
                } else {
                    try {
                        $result = json_decode($this->issueToken($request->withParsedBody($body))->getContent(), true, 512, JSON_THROW_ON_ERROR);
                        Redis::set($key, json_encode($result, JSON_THROW_ON_ERROR, 512), 'EX', 45);
                    } catch (OAuthServerException|Throwable $e) {
                        $result = null;
                    }
                }
            });
        }

        if ($result === null) {
            throw new BadRequestHttpException(__('error.incorrect_refresh_token'));
        }

        return [
            'data'    => $result,
            'message' => __('success.refresh_token_success'),
        ];
    }

    public function revoke(): array
    {
        try {
            $tokenRepository = new TokenRepository();
            $refreshTokenRepository = new RefreshTokenRepository();
            $tokenId = auth()->user()->token()->id;
            $tokenRepository->revokeAccessToken($tokenId);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
        } catch (Throwable $e) {
            throw new BadRequestHttpException(__('error.logout_failed'));
        }

        return [
            'message' => __('success.logout_success'),
        ];
    }

    public function changePassword(Request $request): JsonResponse
    {
        $input = $request->validate([
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|confirmed|min:6'
        ]);

        $user = auth()->user();

        if (Hash::check($input['old_password'], $user->password)) {
            $user->password = Hash::make($input['new_password']);
            $user->save();
        } else {
            throw new BadRequestHttpException(__('error.wrong_password'));
        }

        return response()->json([
            'message' => __('success.change_password_success')
        ]);
    }

    protected function createServerRequest(Request $request): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();
        $factory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        return $factory->createRequest($request);
    }
}
