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
            'akses' => 'karyawan-list',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '1',
            'akses' => 'schedule-list',
        ]);
        DB::table('akses')->insert([
            'id_akses_kategori' => '2',
            'akses' => 'data-bagian',
        ]);
    }
}
