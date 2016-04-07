<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'nickname' => $faker->name,
        'email' => str_random(10).'@gmail.com',
        'password' => bcrypt('secret'),
        'sign' => str_random(10)
    ];
});

$factory->define(App\Repository::class, function ($faker) {
   return [
        'title' => 'test repo title'.str_random(6),
        'creator_id' => rand(1,20),
        'creator_name' => $faker->name,
        'status' => rand(0,1),
        'stars' => rand(0,100),
        'description' => str_random(9)
   ];
});

$factory->define(App\Link::class, function ($faker) {
   return [
        'title' => 'test link title'.str_random(6),
        'url' => str_random(10),
        'description' => str_random(9)
   ];
});

$factory->define(App\Tag::class, function () {
    return [
        'text' => str_random(6),
        'used' => rand(1,10)
    ];
});

$factory->define(App\Tag::class, function () {
    return [
        'text' => str_random(6),
        'used' => rand(1,10)
    ];
});

$factory->define(App\TagItem::class, function() {
    $index[0] = App\Repository;
    $index[1] = App\Link;
    return [
        'tag_id' => rand(1,10),
        'item_id' => rand(1,100),
        'tagitem_id' => $index[rand(0,1)]
    ];
});

$factory->define(App\Starlist::class, function() {
    return [
        'user_id' => rand(1,20),
        'repo_id' => rand(1,100)
    ];
});