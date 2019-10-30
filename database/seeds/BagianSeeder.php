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
        //
        DB::table('bagians')->insert([
            'ruang_id' => '1',
            'departemen_id' => '1',
        ]);
    }
}
