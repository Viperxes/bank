<?php

use App\Account;
use App\Transaction;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});

$factory->define(Account::class, function (Faker $faker) {
    $datetime = $faker->dateTimeThisMonth();

    return [
        'id' => $faker->unique()->randomNumber(7),
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'number' => $faker->unique()->regexify('/1\d{25}/'),
        'balance' => $faker->numberBetween(0, 100000),
        'created_at' => $datetime,
        'updated_at' => $datetime
    ];
});

$factory->define(Transaction::class, function (Faker $faker) {
    $datetime = $faker->dateTimeThisMonth();

    return [
        'id' => $faker->unique()->randomNumber(7),
        'title' => $faker->title,
        'amount' => $faker->numberBetween(1, 10000),
        'created_at' => $datetime,
        'updated_at' => $datetime
    ];
});
