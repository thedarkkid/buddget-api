<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name'=> 'Thedarkkid Codes',
            'email' => 'thedarkkid.codes@gmail.com',
            'password' => Hash::make('secret'),
            'created_at' => (Carbon::now())->toDateTimeString(),
            'updated_at' => (Carbon::now())->toDateTimeString(),
        ]);
    }
}
