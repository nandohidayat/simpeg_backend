<?php

use Illuminate\Database\Seeder;

class ShiftDepartemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shift_departemens')->insert([
            'id_shift' => 1,
            'id_departemen' => 1,
            'status' => true
        ]);
        DB::table('shift_departemens')->insert([
            'id_shift' => 2,
            'id_departemen' => 1,
            'status' => true
        ]);
    }
}
