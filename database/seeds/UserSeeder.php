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
            'username' => 'nando.hidayat',
            'password' => bcrypt('1234'),
            'pegawai_id' => 1,
            'role' => 10
        ]);
        DB::table('users')->insert([
            'username' => 'rama',
            'password' => bcrypt('1234'),
            'pegawai_id' => 2
        ]);
        DB::table('users')->insert([
            'username' => 'candra',
            'password' => bcrypt('1234'),
            'pegawai_id' => 3
        ]);
        DB::table('users')->insert([
            'username' => 'laksita',
            'password' => bcrypt('1234'),
            'pegawai_id' => 4
        ]);
        DB::table('users')->insert([
            'username' => 'raissa',
            'password' => bcrypt('1234'),
            'pegawai_id' => 5
        ]);
        DB::table('users')->insert([
            'username' => 'dini',
            'password' => bcrypt('1234'),
            'pegawai_id' => 6
        ]);
        DB::table('users')->insert([
            'username' => 'julian',
            'password' => bcrypt('1234'),
            'pegawai_id' => 7
        ]);
    }
}
