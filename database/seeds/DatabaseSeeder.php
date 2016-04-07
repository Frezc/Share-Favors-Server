<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\User', 20)->create()->each(function($u) {
            $u->repositories()->save(factory('App\Repository')->make());
        });
        
    }
}
