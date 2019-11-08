<?php

use Illuminate\Database\Seeder;

class RuangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('ruangs')->insert([
            'ruang' => 'Ayyub 1'
        ]);
        DB::table('ruangs')->insert([
            'ruang' => 'Ayyub 2'
        ]);
        DB::table('ruangs')->insert([
            'ruang' => 'Ayyub 3'
        ]);
        DB::table('ruangs')->insert([
            'ruang' => 'SIM'
        ]);
    }
}
