<?php

use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('shifts')->insert([
            'mulai' => date("F d, Y h:i:s", mktime(7, 0, 0, 1, 1, 2011)),
            'selesai' => date("F d, Y h:i:s", mktime(14, 0, 0, 1, 1, 2011)),
            'kode' => 'P',
            'warna' => 'red lighten-3'
        ]);
        DB::table('shifts')->insert([
            'mulai' => date("F d, Y h:i:s", mktime(14, 0, 0, 1, 1, 2011)),
            'selesai' => date("F d, Y h:i:s", mktime(21, 0, 0, 1, 1, 2011)),
            'kode' => 'P',
            'warna' => 'purple lighten-3'
        ]);
    }
}
