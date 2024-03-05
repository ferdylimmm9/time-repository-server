<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TesisBadgesResource;
use App\Http\Resources\Admin\TesisLiteResource;
use App\Http\Resources\Admin\TesisResource;
use App\Models\Enum\StatusTesis;
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
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class TesisController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $tesis = Tesis::query()->with('user')
            ->where('status', $request->input('status', StatusTesis::PENDING));

        if ($request->has('q')) {
            $tesis->where(function ($query) use ($request) {
                $query->where('judul', 'ilike', "%{$request->input('q')}%")
                    ->orWhereRelation('user', 'nama_depan', 'ilike', "%{$request->input('q')}%")
                    ->orWhereRelation('user', 'nama_tengah', 'ilike', "%{$request->input('q')}%")
                    ->orWhereRelation('user', 'nama_belakang', 'ilike', "%{$request->input('q')}%");
            });
        }

        if ($request->has('tipe')) {
            $tesis->where('tipe', 'ilike', "%{$request->input('tipe')}%");
        }

        return TesisLiteResource::collection($tesis->paginate($request->input('limit', 15)));
    }

    public function show(string $id): TesisResource
    {
        return new TesisResource(Tesis::find($id)->load(['user', 'file']));
    }

    public function store(Request $request): TesisResource
    {
        $input = $request->validate([
            "status"        => ['required', new EnumValue(StatusTesis::class)],
            "tipe"          => ['required', new EnumValue(TipeTesis::class)],
            "judul"         => 'required|string',
            "abstrak"       => 'required|string',
            "jurusan_id"    => 'required|exists:jurusan,id',
            "user"          => 'required|array|min:1',
            "user.*.id"     => 'required|exists:user,id|distinct',
            "user.*.urutan" => 'required|numeric',
            "file"          => 'nullable|array',
            "file.*.id"     => 'nullable|exists:file,id',
            "file.*.tipe"   => ['required', new EnumValue(TipeFile::class)],
            "file.*.nama"   => 'required|string',
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

            foreach ($input['file'] as $file) {
                $file = new File($file);
                $file->tesis()->associate($tesis);
                $file->save();
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return (new TesisResource($tesis->load(['user', 'file'])))
            ->additional([
                'message' => __('success.tesis_created')
            ]);
    }

    public function update(Request $request, string $id): TesisResource
    {
        $input = $request->validate([
            'status'        => ['required', new EnumValue(StatusTesis::class)],
            'tipe'          => ['required', new EnumValue(TipeTesis::class)],
            'judul'         => 'required|string',
            'abstrak'       => 'required|string',
            'jurusan_id'    => 'required|exists:jurusan,id',
            "user"          => 'required|array|min:1',
            "user.*.id"     => 'required|exists:user,id|distinct',
            "user.*.urutan" => 'required|numeric',
            'file'          => 'nullable|array',
            'file.*.id'     => 'nullable|exists:file,id',
            'file.*.tipe'   => ['required', new EnumValue(TipeFile::class)],
            'file.*.nama'   => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $tesis = Tesis::find($id);
            $jurusan = Jurusan::find($input['jurusan_id']);
            $tesis->fill($input);
            $tesis->fakultas()->associate($jurusan->fakultas);
            $tesis->save();

            $tesis->user()->detach();

            foreach ($input['user'] as $user) {
                $tesis->user()->attach($user['id'], ['urutan' => $user['urutan']]);
            }

            $tesis_ids = [];
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
                }
            }
            File::where('tesis_id', $tesis->id)->whereNotIn('id', $file_ids)->delete();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return (new TesisResource($tesis->load(['user', 'file'])))
            ->additional([
                'message' => __('success.tesis_updated')
            ]);
    }

    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $tesis = Tesis::find($id);
            $tesis->user()->detach();
            $tesis->file()->delete();
            $tesis->delete();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'message' => __('success.tesis_deleted')
        ]);
    }

    public function badges(): TesisBadgesResource
    {
        $tesis = Tesis::query()
            ->select([
                DB::raw("count(id) filter (where status = '" . StatusTesis::PENDING . "') as pending_tesis_count"),
                DB::raw("count(id) filter (where status = '" . StatusTesis::APPROVED . "') as approved_tesis_count"),
                DB::raw("count(id) filter (where status = '" . StatusTesis::UPLOADING . "') as uploading_tesis_count"),
                DB::raw("count(id) filter (where status = '" . StatusTesis::UPLOADED . "') as uploaded_tesis_count"),
                DB::raw("count(id) filter (where status = '" . StatusTesis::CANCELED . "') as canceled_tesis_count"),
                DB::raw("count(id) filter (where status = '" . StatusTesis::FINISHED . "') as finished_tesis_count"),
                DB::raw("count(id) filter (where status = '" . StatusTesis::TAKEDOWN . "') as takedown_tesis_count"),
            ])->first();

        return new TesisBadgesResource($tesis);
    }
}
