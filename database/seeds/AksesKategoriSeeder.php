<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            'icon' => 'mdi-account-badge'
        ]);
        DB::table('akses_kategoris')->insert([
            'kategori' => 'Database',
            'icon' => 'mdi-database'
        ]);
    }
}
