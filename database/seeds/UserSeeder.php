<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'role_id' => 1,
        ]);

        User::create([
            'name' => 'Gudang',
            'email' => 'gudang@gmail.com',
            'password' => Hash::make('gudang'),
            'role_id' => 2,
        ]);
    }
}
