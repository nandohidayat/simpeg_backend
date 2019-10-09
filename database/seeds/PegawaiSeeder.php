<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $faker = Faker::create('id_ID');

        for ($i = 0; $i < 50; $i++) {
            DB::table('pegawais')->insert([
                'nik' => $faker->numberBetween($min = 1000, $max = 3000),
                'nama' => $faker->name(),
                'jabatans_id' => $faker->numberBetween($min = 0, $max = 5)
            ]);
        }
    }
}
