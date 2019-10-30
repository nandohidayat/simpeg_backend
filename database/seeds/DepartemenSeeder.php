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
            'departemen' => 'Perawat'
        ]);
    }
}
