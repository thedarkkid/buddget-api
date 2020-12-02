<?php

/** @var Factory $factory */

use App\Currency;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Currency::class, function (Faker $faker) {
    return [
        'name' => $faker->country.' '.$faker->currencyCode,
        'acronym' => substr($faker->currencyCode, 0, 3),
    ];
});
