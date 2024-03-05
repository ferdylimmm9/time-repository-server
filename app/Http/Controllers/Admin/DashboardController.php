<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DashboardResource;
use App\Models\Enum\StatusTesis;
use App\Models\Enum\TipeUser;
use App\Models\Tesis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): DashboardResource
    {
        $user = User::query()
            ->select([
                DB::raw("count(id) as total_user"),
                DB::raw("count(id) filter (where tipe_user = '" . TipeUser::ADMIN . "') as admin_count"),
                DB::raw("count(id) filter (where tipe_user = '" . TipeUser::USER . "') as user_count"),
            ])->first();

        $tesis = Tesis::query()
            ->select([
                DB::raw("count(tesis.id) as total_tesis"),
                DB::raw("count(tesis.id) filter (where status = '" . StatusTesis::FINISHED . "') as finished_tesis_count"),
                DB::raw("count(tesis.id) filter (where status = '" . StatusTesis::PENDING . "') as pending_tesis_count"),
            ])->first();

        $dashboard = collect([
            'total_user'           => $user->total_user,
            'admin_count'          => $user->admin_count,
            'user_count'           => $user->user_count,
            'total_tesis'          => $tesis->total_tesis,
            'finished_tesis_count' => $tesis->finished_tesis_count,
            'pending_tesis_count'  => $tesis->pending_tesis_count,
        ]);

        return new DashboardResource($dashboard);
    }
}
