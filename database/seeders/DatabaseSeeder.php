<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Enum\KodeJurusan;
use App\Models\Enum\TipeUser;
use App\Models\Fakultas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $fakultas = new Fakultas();
            $fakultas->nama = 'Informatika';
            $fakultas->save();

            $jurusan = new Jurusan();
            $jurusan->nama = 'Teknik Informatika';
            $jurusan->kode = KodeJurusan::TI;
            $jurusan->fakultas()->associate($fakultas);
            $jurusan->save();

            $user = new User();
            $user->nama_depan = 'Bella';
            $user->nama_belakang = 'Lim';
            $user->tipe_user = TipeUser::USER;
            $user->password = Hash::make('bellalim123');
            $user->nomor_identitas = '123123';
            $user->jurusan()->associate($jurusan);
            $user->save();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
