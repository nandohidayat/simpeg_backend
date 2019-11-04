<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // $this->call(PegawaiSeeder::class);
        // $this->call(JabatanSeeder::class);
        // $this->call(PenilaianSeeder::class);
        // $this->call(RekanSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RuangSeeder::class);
        $this->call(DepartemenSeeder::class);
        $this->call(KaryawanSeeder::class);
        $this->call(ShiftSeeder::class);
    }
}
