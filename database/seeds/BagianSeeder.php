<?php

use Illuminate\Database\Seeder;

class BagianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bagians')->insert([
            'bagian' => 'Non Medis',
        ]);
        DB::table('bagians')->insert([
            'bagian' => 'Medis',
        ]);
    }
}
