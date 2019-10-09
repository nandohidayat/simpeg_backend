<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class JabatanSeeder extends Seeder
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

        for ($i = 0; $i < 20; $i++) {
            DB::table('jabatans')->insert([
                'bagian' => floor($i / 3),
                'tingkat' => ($i % 3),
                'posisi' => $faker->jobTitle()
            ]);
        }
    }
}
