<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\TesisLiteResource;
use App\Http\Resources\User\TesisResource;
use App\Models\Enum\TipeFile;
use App\Models\Enum\TipeTesis;
use App\Models\File;
use App\Models\Jurusan;
use App\Models\Tesis;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class TesisController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $tesis = Tesis::query()->with('user');

        if ($request->has('q')) {
            $tesis->where(function ($query) use ($request) {
                $query->where('judul', 'ilike', "%{$request->input('q')}%")
                    ->orWhereRelation('user', 'nama_depan', 'ilike', "%{$request->input('q')}%")
                    ->orWhereRelation('user', 'nama_tengah', 'ilike', "%{$request->input('q')}%")
                    ->orWhereRelation('user', 'nama_belakang', 'ilike', "%{$request->input('q')}%");
            });
        }

        $tesis = QueryBuilder::for($tesis)
            ->defaultSort('judul')
            ->allowedSorts('judul', 'waktu_dibuat');

        return TesisLiteResource::collection($tesis->paginate($request->input('limit', 15)));
    }

    public function show(string $id): TesisResource
    {
        return new TesisResource(Tesis::find($id)->load(['user', 'file']));
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->validate([
            "tipe"          => ['required', new EnumValue(TipeTesis::class)],
            "judul"         => 'required|string',
            "abstrak"       => 'required|string',
            "jurusan_id"    => 'required|exists:jurusan,id',
            "user"          => 'required|array|min:1',
            "user.*.id"     => 'required|exists:user,id|distinct',
            "user.*.urutan" => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $jurusan = Jurusan::find($input['jurusan_id']);
            $tesis = new Tesis($input);
            $tesis->fakultas()->associate($jurusan->fakultas);
            $tesis->save();

            foreach ($input['user'] as $user) {
                $tesis->user()->attach($user['id'], ['urutan' => $user['urutan']]);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'message' => __('success.tesis_created')
        ]);
    }

    public function updateFile(Request $request, string $id): TesisResource
    {
        $input = $request->validate([
            'file'        => 'nullable|array',
            'file.*.id'   => 'nullable|exists:file,id',
            'file.*.tipe' => ['required', new EnumValue(TipeFile::class)],
            'file.*.nama' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $tesis = Tesis::find($id);

            $file_ids = [];
            if (isset($input['file']) && !empty($input['file'])) {
                foreach ($input['file'] as $input) {
                    if (isset($input['id'])) {
                        $file = File::find($input['id']);
                    } else {
                        $file = new File();
                        $file->tesis()->associate($tesis);
                    }
                    $file->fill($input);
                    $file->save();

                    $file_ids[] = $file->id;
                }
            }
            File::where('tesis_id', $tesis->id)->whereNotIn('id', $file_ids)->delete();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return (new TesisResource($tesis))
            ->additional([
                'message' => __('success.update_file_success')
            ]);
    }
}
