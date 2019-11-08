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
            'id_departemen' => 1,
            'id_ruang' => 4
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1660',
            'nama' => 'Raissa Almira Rachmayanti',
            'id_departemen' => 2,
            'id_ruang' => 4
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1661',
            'nama' => 'Laksita Kusuma Wardhani',
            'id_departemen' => 3,
            'id_ruang' => 1
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1662',
            'nama' => 'Yulia Dini Hakiki',
            'id_departemen' => 4,
            'id_ruang' => 1
        ]);
        DB::table('karyawans')->insert([
            'nik' => '1663',
            'nama' => 'Dwi Fitri Lestari',
            'id_departemen' => 4,
            'id_ruang' => 1
        ]);
    }
}
