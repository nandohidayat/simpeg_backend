<?php

use Illuminate\Database\Seeder;

class DepartemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('departemens')->insert([
            'departemen' => 'Kepala SIM',
            'tingkat' => '1',
            'id_bagian' => '1'
        ]);
        DB::table('departemens')->insert([
            'departemen' => 'SIM',
            'tingkat' => '2',
            'id_bagian' => '1'
        ]);
        DB::table('departemens')->insert([
            'departemen' => 'Kepala Perawat',
            'tingkat' => '1',
            'id_bagian' => '2'
        ]);
        DB::table('departemens')->insert([
            'departemen' => 'Perawat',
            'tingkat' => '2',
            'id_bagian' => '2'
        ]);
    }
}
