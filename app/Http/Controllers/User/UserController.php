<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserLiteResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $user = User::query()->with('jurusan');

        if ($request->has('q')) {
            $user->where(function ($query) use ($request) {
                $query->where('nama', 'ilike', "%{$request->input('q')}%")
                    ->orWhere('nomor_identitas', 'ilike', "%{$request->input('q')}%");
            });
        }

        if ($request->has('jurusan')) {
            $user->whereRelation('jurusan', 'nama', 'ilike', "%{$request->input('jurusan')}%");
        }

        return UserLiteResource::collection($user->paginate($request->input('limit', 15)));
    }

    public function show(string $id): UserResource
    {
        return new UserResource(User::find($id)->load('tesis.user'));
    }
}
