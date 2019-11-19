<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'nik' => 1659,
            'username' => 'nando.hidayat',
            'password' => bcrypt('1234'),
        ]);
        DB::table('users')->insert([
            'nik' => 1661,
            'username' => 'laksita.kusuma',
            'password' => bcrypt('1234'),
        ]);
    }
}
