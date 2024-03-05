<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\FakultasResource;
use App\Models\Fakultas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FakultasController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $fakultas = Fakultas::query();

        if ($request->has('q')) {
            $fakultas->where('name', 'ilike', "%{$request->input('q')}%");
        }

        return FakultasResource::collection($fakultas->paginate($request->input('limit', 15)));
    }

    public function show(string $id): FakultasResource
    {
        return new FakultasResource(Fakultas::find($id));
    }

    public function store(Request $request): FakultasResource
    {
        $input = $request->validate([
            'nama' => 'required|string',
        ]);

        $fakultas = new Fakultas($input);
        $fakultas->save();

        return (new FakultasResource($fakultas))
            ->additional([
                'message' => __('success.fakultas_created')
            ]);
    }

    public function update(Request $request, string $id): FakultasResource
    {
        $input = $request->validate([
            'nama' => 'required|string',
        ]);

        $fakultas = Fakultas::find($id);
        $fakultas->fill($input);
        $fakultas->save();

        return (new FakultasResource($fakultas))
            ->additional([
                'message' => __('success.fakultas_updated')
            ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $fakultas = Fakultas::find($id);
        if ($fakultas->jurusans()->exists()) {
            throw new BadRequestHttpException(__('error.jurusan_exists'));
        }

        $fakultas->delete();

        return response()->json([
            'message' => __('success.fakultas_deleted')
        ]);
    }
}
