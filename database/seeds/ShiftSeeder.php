<?php

use Carbon\Carbon;
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
            'mulai' => Carbon::createFromTime(7, 0, 0, 'Asia/Jakarta'),
            'selesai' => Carbon::createFromTime(14, 0, 0, 'Asia/Jakarta'),
            'kode' => 'P',
            'warna' => 'red lighten-3'
        ]);
        DB::table('shifts')->insert([
            'mulai' => Carbon::createFromTime(14, 0, 0, 'Asia/Jakarta'),
            'selesai' => Carbon::createFromTime(21, 0, 0, 'Asia/Jakarta'),
            'kode' => 'S',
            'warna' => 'purple lighten-3'
        ]);
    }
}
