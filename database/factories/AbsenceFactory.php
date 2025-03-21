<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Absence;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Absence::class, function (Faker $faker) {
    $reasons = [
        'vacation',
        'vacation_day',
        'time_off',
        'flextime',
        'sick_leave',
        'personal_leave',
        'time_off_in_lieu',
        'other',
    ];

    return [
        'external_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'reason' => $faker->randomElement($reasons),
        'start_at' => now(),
        'end_at' => now()->addDays(3),
        'user_id' => factory(User::class),
    ];
});

