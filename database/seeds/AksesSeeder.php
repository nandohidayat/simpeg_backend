<?php

use Illuminate\Database\Seeder;

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
            'url' => 'karyawan-list',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '1',
            'akses' => 'Jadwal Karyawan',
            'url' => 'schedule-list',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '2',
            'akses' => 'Data Karyawan',
            'url' => 'data-karyawan',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '2',
            'akses' => 'Hak Akses',
            'url' => 'akses-list',
        ]);
    }
}