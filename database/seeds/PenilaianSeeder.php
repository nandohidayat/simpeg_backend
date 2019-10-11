<?php

use Illuminate\Database\Seeder;

class PenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('penilaians')->insert([
            'mulai' => date('2019-10-10'),
            'selesai' => date('2019-10-17'),
            'pegawais_id' => 1
        ]);
        DB::table('penilaians')->insert([
            'mulai' => date('2019-10-11'),
            'selesai' => date('2019-10-18'),
            'pegawais_id' => 1
        ]);
    }
}
