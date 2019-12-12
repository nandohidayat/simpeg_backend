<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('akses')->insert([
            'id_akses_kategori' => '1',
            'akses' => 'Daftar Karyawan',
            'url' => '/karyawan',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '1',
            'akses' => 'Jadwal Karyawan',
            'url' => '/schedule',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '2',
            'akses' => 'Data Karyawan',
            'url' => '/data/karyawan',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '2',
            'akses' => 'Hak Akses',
            'url' => '/data/akses',
        ]);
    }
}
