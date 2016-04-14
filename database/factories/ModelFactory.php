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
use App\User;
use App\Repository;
$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'nickname' => $faker->name,
        'email' => str_random(10).'@gmail.com',
        'password' => bcrypt('secret'),
        'sign' => $faker->realText($maxNbChars = 10, $indexSize = 2)
    ];
});

$factory->define(App\Repository::class, function ($faker) {
   $creator_id = rand(1,20);
   $user = User::find($creator_id);
   return [
        'title' => 'test repo title '.$faker->realText($maxNbChars = 10, $indexSize = 2),
        'creator_id' => $creator_id,
        'creator_name' => $user->nickname,
        'status' => rand(0,1),
        'stars' => rand(0,100),
        'description' => $faker->realText($maxNbChars = 200, $indexSize = 2)
   ];
});

$factory->define(App\Link::class, function ($faker) {
   return [
        'title' => 'test link title '.$faker->realText($maxNbChars = 10, $indexSize = 2),
        'url' => $faker->url,
        'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
        'repo_id' => rand(1,100)
   ];
});

$factory->define(App\Tag::class, function () {
    return [
        'text' => str_random(6),
        'used' => rand(1,10)
    ];
});

$factory->define(App\TagItem::class, function() {
    $index[0] = 'App\Repository';
    $index[1] = 'App\Link';
    return [
        'tag_id' => rand(1,10),
        'item_id' => rand(1,2000),
        'tagitems_type' => $index[rand(0,1)]
        
    ];
});

$factory->define(App\Starlist::class, function() {
    return [
        'user_id' => rand(1,20),
        'repo_id' => rand(1,200)
    ];
});

$factory->define(App\Repolist::class, function() {
   return [
      'repo_id' => rand(1,100),
      'type' => rand(0,1),
      'item_id' => rand(1,2000),
      'status' => rand(0,1)
   ]; 
});