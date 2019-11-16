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
            'kategori' => 'karyawan',
        ]);
        DB::table('akses_kategoris')->insert([
            'kategori' => 'database',
        ]);
    }
}
