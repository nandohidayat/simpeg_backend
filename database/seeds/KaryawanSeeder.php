<?php

use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('karyawans')->insert([
            'nik' => '1659',
            'nama' => 'Muhammad Nando Hidayat',
            'departemen_id' => 1,
            'ruang_id' => 1
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1660',
            'nama' => 'Raissa Almira Rachmayanti',
            'departemen_id' => 1,
            'ruang_id' => 1
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1661',
            'nama' => 'Laksita Kusuma Wardhani',
            'departemen_id' => 1,
            'ruang_id' => 1
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1662',
            'nama' => 'Yulia Dini Hakiki',
            'departemen_id' => 1,
            'ruang_id' => 2
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1663',
            'nama' => 'Dwi Fitri Lestari',
            'departemen_id' => 1,
            'ruang_id' => 2
        ]);
    }
}
