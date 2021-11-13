<?php

use App\Barang;
use Faker\Factory;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create('id_ID');

        for ($i = 0; $i < 10; $i++) {
            Barang::create([
                'kode_barang' => $faker->ean13(),
                'nama_barang' => $faker->text(50),
                'kategori_id' => $faker->numberBetween(1, 10),
                'keterangan' => $faker->text(100),
            ]);
        }
    }
}
