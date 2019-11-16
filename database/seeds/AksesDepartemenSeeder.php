<?php

use Illuminate\Database\Seeder;

class AksesDepartemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('akses_departemens')->insert([
            'id_akses' => '3',
            'id_departemen' => '1',
        ]);
        DB::table('akses_departemens')->insert([
            'id_akses' => '1',
            'id_departemen' => '3',
        ]);
    }
}
