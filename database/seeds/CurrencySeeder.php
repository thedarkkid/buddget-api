<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert(
            [
                'name' => 'Nigerian Naira',
                'acronym' => 'NGN',
                'created_at' => (Carbon::now())->toDateTimeString(),
                'updated_at' => (Carbon::now())->toDateTimeString(),
            ]
        );
        DB::table('currencies')->insert(

            [
                'name' => 'US Dollar',
                'acronym' => 'USD',
                'created_at' => (Carbon::now())->toDateTimeString(),
                'updated_at' => (Carbon::now())->toDateTimeString(),
            ]
        );
        DB::table('currencies')->insert(
            [
                'name' => 'Canadian Dollar',
                'acronym' => 'CAD',
                'created_at' => (Carbon::now())->toDateTimeString(),
                'updated_at' => (Carbon::now())->toDateTimeString(),
            ]
        );
        DB::table('currencies')->insert(
            [
                'name' => 'British Pound',
                'acronym' => 'GBP',
                'created_at' => (Carbon::now())->toDateTimeString(),
                'updated_at' => (Carbon::now())->toDateTimeString(),
            ],
        );
    }
}
