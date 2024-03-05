<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TesisLiteResource;
use App\Http\Resources\Admin\UserLiteResource;
use App\Http\Resources\Admin\UserResource;
use App\Models\Enum\TipeUser;
use App\Models\Tesis;
use App\Models\User;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $user = User::query()->with('jurusan')->orderBy('nomor_identitas');

        if ($request->has('q')) {
            $user->where('nomor_identitas', 'ilike', "{$request->get('q')}")
                ->orWhere('nama_depan', 'ilike', "%{$request->get('q')}%");
        }

        if ($request->has('tipe')) {
            $user->where('tipe_user', '=', $request->get('tipe'));
        }

        return UserLiteResource::collection($user->paginate($request->input('limit', 15)));
    }

    public function show(string $id): UserResource
    {
        return new UserResource(User::find($id));
    }

    public function store(Request $request): UserResource
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

        return (new UserResource($user))
        ->additional([
            'message' => __('success.user_created')
        ]);
    }

    public function update(string $id, Request $request): UserResource
    {
        $user = User::find($id);
        $input = $request->validate([
            'nomor_identitas' => ['required', 'string', Rule::unique('user', 'nomor_identitas')->ignoreModel($user)],
            'nama_depan'      => 'required|string',
            'nama_tengah'     => 'nullable|string',
            'nama_belakang'   => 'nullable|string',
            'jurusan_id'      => 'required|exists:jurusan,id',
            'password'        => 'required|string|confirmed|min:6',
            'tipe_user'       => ['required', new EnumValue(TipeUser::class)],
        ]);

        $user->fill($input);
        $user->password = Hash::make($input['password']);
        $user->save();

        return (new UserResource($user))
        ->additional([
            'message' => __('success.user_updated')
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);
        if ($user->tesises()->exists()) {
            throw new BadRequestHttpException(__('error.user_has_tesis'));
        }
        $user->delete();

        return response()->json([
            'message' => __('success.user_deleted')
        ]);
    }

    public function tesis(string $id, Request $request): ResourceCollection
    {
        $tesis = Tesis::select('tesis.*')
            ->join('user_tesis', 'user_tesis.tesis_id', 'tesis.id')
            ->where('user_id', $id)->with('user');

        return TesisLiteResource::collection($tesis->paginate($request->input('limit', 15)));
    }
}
