<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AksesDepartemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('akses_departemens')->insert([
        //     'id_akses' => '3',
        //     'id_departemen' => '1',
        //     'status' => true
        // ]);
        // DB::table('akses_departemens')->insert([
        //     'id_akses' => '4',
        //     'id_departemen' => '1',
        //     'status' => true
        // ]);
        // DB::table('akses_departemens')->insert([
        //     'id_akses' => '1',
        //     'id_departemen' => '1',
        //     'status' => false
        // ]);
        // DB::table('akses_departemens')->insert([
        //     'id_akses' => '1',
        //     'id_departemen' => '3',
        //     'status' => true
        // ]);
        DB::table('akses_departemens')->insert([
            'id_akses' => '1',
            'id_dept' => 'd-33',
            'status' => true
        ]);
        DB::table('akses_departemens')->insert([
            'id_akses' => '2',
            'id_dept' => 'd-33',
            'status' => true
        ]);
        DB::table('akses_departemens')->insert([
            'id_akses' => '3',
            'id_dept' => 'd-33',
            'status' => true
        ]);
        DB::table('akses_departemens')->insert([
            'id_akses' => '4',
            'id_dept' => 'd-33',
            'status' => true
        ]);
    }
}
