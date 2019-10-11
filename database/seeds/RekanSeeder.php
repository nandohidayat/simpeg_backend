<?php

use Illuminate\Database\Seeder;

class RekanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('rekans')->insert([
            'penilaian_id' => 1,
            'pegawai_id' => 7,
            'tingkat' => 1
        ]);
        DB::table('rekans')->insert([
            'penilaian_id' => 1,
            'pegawai_id' => 2,
            'tingkat' => 2
        ]);
        DB::table('rekans')->insert([
            'penilaian_id' => 1,
            'pegawai_id' => 3,
            'tingkat' => 2
        ]);
        DB::table('rekans')->insert([
            'penilaian_id' => 1,
            'pegawai_id' => 4,
            'tingkat' => 3
        ]);
        DB::table('rekans')->insert([
            'penilaian_id' => 1,
            'pegawai_id' => 5,
            'tingkat' => 3
        ]);
        DB::table('rekans')->insert([
            'penilaian_id' => 1,
            'pegawai_id' => 6,
            'tingkat' => 3
        ]);
    }
}
