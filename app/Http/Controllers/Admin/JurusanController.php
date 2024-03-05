<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\JurusanResource;
use App\Http\Resources\Admin\JurusanLiteResource;
use App\Models\Jurusan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class JurusanController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $jurusan = Jurusan::query();

        if ($request->has('q')) {
            $jurusan->where('nama', 'ilike', "%{$request->input('q')}%");
        }

        return JurusanLiteResource::collection($jurusan->paginate($request->input('limit', 15)));
    }

    public function show(string $id): JurusanResource
    {
        return new JurusanResource(Jurusan::find($id));
    }

    public function store(Request $request): JurusanResource
    {
        $input = $request->validate([
            'nama'        => 'required|string',
            'kode'        => 'required|string|unique:jurusan,kode',
            'fakultas_id' => 'required|exists:fakultas,id',
        ]);

        $jurusan = new Jurusan($input);
        $jurusan->save();

        return (new JurusanResource($jurusan))
            ->additional([
                'message' => __('success.jurusan_created')
            ]);
    }

    public function update(Request $request, string $id): JurusanResource
    {
        $input = $request->validate([
            'nama'        => 'required|string',
            'kode'        => 'required|string|unique:jurusan,kode,' . $id,
            'fakultas_id' => 'required|exists:fakultas,id',
        ]);

        $jurusan = Jurusan::find($id);
        $jurusan->fill($input);
        $jurusan->save();

        return (new JurusanResource($jurusan))
            ->additional([
                'message' => __('success.jurusan_updated')
            ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $jurusan = Jurusan::find($id);

        if ($jurusan->users()->exists() || $jurusan->tesis()->exists()) {
            throw new BadRequestHttpException(__('error.user_tesis_exists'));
        }

        $jurusan->delete();

        return response()->json([
            'message' => __('success.jurusan_deleted')
        ]);
    }
}
