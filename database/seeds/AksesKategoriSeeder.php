<?php

use Illuminate\Database\Seeder;

class AksesKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('akses_kategoris')->insert([
            'kategori' => 'Karyawan',
        ]);
        DB::table('akses_kategoris')->insert([
            'kategori' => 'Database',
        ]);
    }
}
